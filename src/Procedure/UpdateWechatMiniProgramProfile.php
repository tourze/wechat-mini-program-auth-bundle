<?php

namespace WechatMiniProgramAuthBundle\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ConnectException;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\JsonRPCLogBundle\Procedure\LogFormatProcedure;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Enum\Language;
use WechatMiniProgramAuthBundle\Exception\DecryptException;
use WechatMiniProgramAuthBundle\Repository\CodeSessionLogRepository;
use WechatMiniProgramAuthBundle\Repository\UserRepository;
use WechatMiniProgramAuthBundle\Request\CodeToSessionRequest;
use WechatMiniProgramAuthBundle\Service\EncryptService;
use WechatMiniProgramBundle\Service\AccountService;
use WechatMiniProgramBundle\Service\Client;
use Yiisoft\Json\Json;

/**
 * 因微信有新规，所以这个接口实际到 2022 年 10 月 25 日 24 时后就不能继续使用。
 *
 * @see https://developers.weixin.qq.com/miniprogram/dev/api/open-api/user-info/wx.getUserProfile.html
 * @see https://developers.weixin.qq.com/community/develop/doc/00022c683e8a80b29bed2142b56c01
 */
#[MethodTag('微信小程序')]
#[MethodDoc('更新微信个人信息')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Log]
#[MethodExpose('UpdateWechatMiniProgramProfile')]
#[WithMonologChannel('procedure')]
class UpdateWechatMiniProgramProfile extends LockableProcedure implements LogFormatProcedure
{
    #[MethodParam('AppID')]
    public string $appId = '';

    #[MethodParam('云用户ID')]
    public ?string $cloudID = null;

    #[MethodParam('code')]
    public string $code;

    #[MethodParam('iv')]
    public string $iv;

    #[MethodParam('encryptedData')]
    public string $encryptedData;

    public function __construct(
        private readonly AccountService $accountService,
        private readonly Client $client,
        private readonly EncryptService $encryptService,
        private readonly CodeSessionLogRepository $codeSessionLogRepository,
        private readonly RequestStack $requestStack,
        private readonly LoggerInterface $logger,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $account = $this->accountService->detectAccountFromRequest($this->requestStack->getMainRequest(), $this->appId);
        if (!$account) {
            throw new ApiException('找不到小程序');
        }

        // 前端是有可能传入重复的code，我们在这里做一层兼容
        $log = $this->codeSessionLogRepository->findOneBy([
            'code' => $this->code,
            'account' => $account,
        ]);
        if (!$log) {
            $request = new CodeToSessionRequest();
            $request->setAppId($account->getAppId());
            $request->setSecret($account->getAppSecret());
            $request->setJsCode($this->code);

            try {
                $session = $this->client->request($request);
            } catch (ConnectException $exception) {
                throw new ApiException('微信接口超时，请稍后重试', previous: $exception);
            }

            if (!isset($session['session_key'])) {
                throw new ApiException('微信登录失败，请重新进入小程序');
            }

            // 每次授权，我们都记录一次，这样好统计打开数
            $log = new CodeSessionLog();
            $log->setAccount($account);
            $log->setCode($this->code);
            $log->setOpenId($session['openid']);
            $log->setUnionId($session['unionid'] ?? '');
            $log->setSessionKey($session['session_key'] ?? '');
            $log->setRawData(Json::encode($session));
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        }

        try {
            $data = $this->encryptService->decryptData($log->getSessionKey(), $this->iv, $this->encryptedData);
        } catch (DecryptException $exception) {
            throw new ApiException('微信数据异常，请重试', 0, [], $exception);
        }

        $this->logger->info('解密获得微信用户UserProfile', $data);

        // 更新微信用户
        $user = $this->userRepository->findOneBy([
            'openId' => $log->getOpenId(),
            'account' => $account,
        ]);
        if (!$user) {
            $user = new User();
            $user->setAccount($account);
            $user->setOpenId($log->getOpenId());
            $user->setUnionId($log->getUnionId());
            $user->setLanguage(Language::zh_CN);
        }

        $user->setNickName($data['nickName']);
        $user->setAvatarUrl($data['avatarUrl']);
        $user->setLanguage(Language::tryFrom($data['language']) ?: Language::zh_CN);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->userRepository->transformToBizUser($user);

        return [
            '__message' => '授权成功',
        ];
    }

    public function generateFormattedLogText(JsonRpcRequest $request): string
    {
        return '授权微信小程序用户信息';
    }
}

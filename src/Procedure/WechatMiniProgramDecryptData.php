<?php

namespace WechatMiniProgramAuthBundle\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\HttpFoundation\RequestStack;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Repository\CodeSessionLogRepository;
use WechatMiniProgramAuthBundle\Request\CodeToSessionRequest;
use WechatMiniProgramAuthBundle\Service\EncryptService;
use WechatMiniProgramBundle\Service\AccountService;
use WechatMiniProgramBundle\Service\Client;
use Yiisoft\Json\Json;

/**
 * 因为一般不会单独使用这个接口，所以其实作用不大了。。
 */
#[MethodDoc('解密微信加密数据')]
#[MethodTag('微信小程序')]
class WechatMiniProgramDecryptData extends LockableProcedure
{
    #[MethodParam('AppID')]
    public string $appId = '';

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

        return $this->encryptService->decryptData($log->getSessionKey(), $this->iv, $this->encryptedData);
    }
}

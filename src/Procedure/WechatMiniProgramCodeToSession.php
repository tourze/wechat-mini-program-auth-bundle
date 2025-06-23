<?php

namespace WechatMiniProgramAuthBundle\Procedure;

use AccessTokenBundle\Service\AccessTokenService;
use Carbon\CarbonImmutable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use HttpClientBundle\Exception\HttpClientException;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\DoctrineUpsertBundle\Service\UpsertManager;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\LoginProtectBundle\Service\LoginService;
use Tourze\UserIDBundle\Model\SystemUser;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Enum\Language;
use WechatMiniProgramAuthBundle\Event\CodeToSessionRequestEvent;
use WechatMiniProgramAuthBundle\Event\CodeToSessionResponseEvent;
use WechatMiniProgramAuthBundle\Repository\CodeSessionLogRepository;
use WechatMiniProgramAuthBundle\Repository\UserRepository;
use WechatMiniProgramAuthBundle\Request\CodeToSessionRequest;
use WechatMiniProgramBundle\Procedure\LaunchOptionsAware;
use WechatMiniProgramBundle\Service\AccountService;
use WechatMiniProgramBundle\Service\Client;
use Yiisoft\Json\Json;

#[MethodDoc('微信小程序初始化code2session接口')]
#[MethodTag('微信小程序')]
#[MethodExpose('WechatMiniProgramCodeToSession')]
#[Log]
#[WithMonologChannel('procedure')]
class WechatMiniProgramCodeToSession extends LockableProcedure
{
    use LaunchOptionsAware;

    /**
     * @var string 前端调用 wx.getAccountInfoSync 获得的信息
     *
     * @see https://developers.weixin.qq.com/miniprogram/dev/api/open-api/account-info/wx.getAccountInfoSync.html
     * @see https://developers.weixin.qq.com/miniprogram/dev/api/open-api/login/wx.login.html
     * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/login/auth.code2Session.html
     */
    #[MethodParam('前端调用 wx.getAccountInfoSync 获得的信息')]
    public string $appId = '';

    #[MethodParam('wx.login获得的code，如果想模拟指定OpenID登录，可以传入 mock_{"openid":"XXX","unionid":"YYY","session_key":"123"}')]
    #[Assert\NotNull]
    public string $code;

    public function __construct(
        private readonly AccountService $accountService,
        private readonly CodeSessionLogRepository $codeSessionLogRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UpsertManager $upsertManager,
        private readonly Client $client,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly UserLoaderInterface $userLoader,
        private readonly AccessTokenService $accessTokenService,
        private readonly RequestStack $requestStack,
        private readonly LoginService $loginService,
        private readonly Security $security,
        private readonly LoggerInterface $logger,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function execute(): array
    {
        $account = $this->accountService->detectAccountFromRequest($this->requestStack->getMainRequest(), $this->appId);
        if ($account === null) {
            throw new ApiException('找不到小程序');
        }

        $request = new CodeToSessionRequest();
        $request->setAppId($account->getAppId());
        $request->setSecret($account->getAppSecret());
        $request->setJsCode($this->code);

        try {
            $session = $this->client->request($request);
        } catch (HttpClientException $exception) {
            // invalid code, rid: 64c8b2ae-2d255da6-372e4494
            if (str_contains($exception->getMessage(), 'invalid code, rid')) {
                throw new ApiException('微信登录态无效，请返回重试', 0, [], $exception);
            }
            throw $exception;
        }

        if (!isset($session['session_key'])) {
            // 前端是有可能传入重复的code，我们在这里做一层兼容
            $log = $this->codeSessionLogRepository->findOneBy([
                'code' => $this->code,
                'account' => $account,
            ]);
            if ($log === null) {
                throw new ApiException('微信登录失败，请重新进入小程序[1]');
            }
            $now = CarbonImmutable::now();
            if (abs($now->diffInSeconds($log->getCreateTime())) > 10) {
                throw new ApiException('微信登录失败，请重新进入小程序[2]');
            }
        } else {
            // 每次授权，我们都记录一次，这样好统计打开数
            $log = new CodeSessionLog();
            $log->setAccount($account);
            $log->setCode($this->code);
            $log->setOpenId($session['openid']);
            $log->setUnionId($session['unionid'] ?? '');
            $log->setSessionKey($session['session_key']);
            $log->setRawData(Json::encode($session));
            $log->setLaunchOptions($this->launchOptions);
            $log->setEnterOptions($this->enterOptions);
            $log->setCreatedFromIp($this->requestStack->getMainRequest()?->getClientIp());
            $log = $this->upsertManager->upsert($log);
        }

        $event = new CodeToSessionRequestEvent();
        $event->setAccount($account);
        $event->setCode($this->code);
        $event->setCodeSessionLog($log);
        $event->setLaunchOptions($this->launchOptions);
        $event->setEnterOptions($this->enterOptions);
        $event->setBizUser($this->security->getUser());
        $this->eventDispatcher->dispatch($event);
        $return = $event->getReturn();
        if ($return !== null) {
            return $return;
        }

        $result = [
            // TODO 移除sessionKey
            'sessionKey' => null,
            'openId' => $log->getOpenId(),
            'unionId' => $log->getUnionId(),
        ];

        // 保存到微信用户信息表
        $wechatUser = new User();
        $wechatUser->setAccount($account);
        $wechatUser->setLanguage(Language::zh_CN);
        $wechatUser->setOpenId($log->getOpenId());
        if ($log->getUnionId()) {
            // 更新UnionID
            $wechatUser->setUnionId($log->getUnionId());
        }
        $wechatUser = $this->upsertManager->upsert($wechatUser);
        \assert($wechatUser instanceof User);

        $bizUser = null;
        $isNewUser = false;
        $this->checkUser($wechatUser, $result, $isNewUser, $bizUser);
        
        // 如果没有找到对应的系统用户，通过 UserRepository 创建
        if ($bizUser === null) {
            $bizUser = $this->userRepository->transformToSysUser($wechatUser);
            $isNewUser = true;
        }

        // 既然每次都是这个鬼样，那么用户就不用再提供啥刷新信息的机制了
        if (method_exists($bizUser, 'retrieveApiArray')) {
            $result['user'] = $bizUser->retrieveApiArray();
        } else {
            // 如果没有 retrieveApiArray 方法，返回基本信息
            $result['user'] = [
                'id' => $bizUser->getUserIdentifier(),
                'username' => $bizUser->getUserIdentifier(),
            ];
        }
        $result['jwt'] = $this->accessTokenService->createToken($bizUser);

        // 补充返回手机号码信息
        $result['phoneNumbers'] = [];
        if (method_exists($bizUser, 'getMobile') && $bizUser->getMobile()) {
            $result['phoneNumbers'][] = $bizUser->getMobile();
        }
        foreach ($wechatUser->getPhoneNumbers() as $phoneNumber) {
            $result['phoneNumbers'][] = $phoneNumber->getPhoneNumber();
        }

        $event = new CodeToSessionResponseEvent();
        $event->setSender($bizUser);
        $event->setReceiver(SystemUser::instance());
        $event->setMessage('打开了微信小程序(code2session)');
        $event->setBizUser($bizUser);
        $event->setWechatUser($wechatUser);
        $event->setCodeSessionLog($log);
        $event->setResult($result);
        $event->setLaunchOptions($this->launchOptions);
        $event->setEnterOptions($this->enterOptions);
        $event->setNewUser($isNewUser);
        $this->eventDispatcher->dispatch($event);

        $result = $event->getResult();
        if ((bool) isset($result['phoneNumbers'])) {
            // 去重
            $result['phoneNumbers'] = array_unique($result['phoneNumbers']);
            $result['phoneNumbers'] = array_values($result['phoneNumbers']);
        }

        $this->loginService->saveLoginLog($bizUser, 'success');

        return $result;
    }

    public function getLockResource(JsonRpcParams $params): ?array
    {
        return [
            'WechatMiniProgramCodeToSession' . $params->get('code'),
        ];
    }

    protected function getIdempotentCacheKey(JsonRpcRequest $request): string
    {
        return 'WechatMiniProgramCodeToSession-idempotent-' . $request->getParams()->get('code');
    }

    protected function checkUser(User $wechatUser, &$result, &$isNewUser, &$bizUser)
    {
        // 检查系统用户表是否也有记录了
        $bizUser = $this->userLoader->loadUserByIdentifier($wechatUser->getOpenId());
        // 按照微信的说法，"目前小程序开发者可以通过 wx.login 接口直接获取用户的 openId 与 unionId 信息，实现微信身份登录"
        // 我们在后续的使用过程中无法直接拿到用户数据，那么就只能在这里就生成一个用户数据了。。

        // 有一种特殊情况，就是我们通过第三方接口，保存了一个临时用户，这种用户信息，我们需要额外修正的
        if ($bizUser === null && !empty($result['unionId'])) {
            $bizUser = $this->userLoader->loadUserByIdentifier("temp_{$result['unionId']}");
        }

        if ($bizUser === null) {
            $isNewUser = true;
            // 不再直接创建 BizUser，改为通过 UserRepository 的 transformToSysUser 方法
            // 这样可以保持代码的解耦性
        }

        if (!empty($wechatUser->getUnionId()) && $bizUser !== null && method_exists($bizUser, 'setIdentity')) {
            $bizUser->setIdentity($wechatUser->getUnionId());
        }

        try {
            // 一般来说不应该发生这种事情，但是真的有发生。。。只能先这样处理了
            $this->entityManager->persist($bizUser);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            $this->logger->error('遇到了索引冲突报错：' . $exception->getMessage(), [
                'exception' => $exception,
                'bizUser' => $bizUser,
                'wechatUser' => $wechatUser,
            ]);
            throw new ApiException('请点击右上角，重新进入小程序');
        }
    }
}

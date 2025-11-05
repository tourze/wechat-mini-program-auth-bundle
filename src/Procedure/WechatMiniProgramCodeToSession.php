<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Procedure;

use Carbon\CarbonImmutable;
use HttpClientBundle\Exception\HttpClientException;
use Monolog\Attribute\WithMonologChannel;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\AccessTokenContracts\TokenServiceInterface;
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
use Tourze\LockServiceBundle\Service\LockService;
use Tourze\LoginProtectBundle\Service\LoginService;
use Tourze\UserIDBundle\Model\SystemUser;
use Tourze\UserServiceContracts\UserManagerInterface;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Enum\Language;
use WechatMiniProgramAuthBundle\Event\CodeToSessionRequestEvent;
use WechatMiniProgramAuthBundle\Event\CodeToSessionResponseEvent;
use WechatMiniProgramAuthBundle\Repository\CodeSessionLogRepository;
use WechatMiniProgramAuthBundle\Request\CodeToSessionRequest;
use WechatMiniProgramAuthBundle\Service\UserTransformService;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Procedure\LaunchOptionsAware;
use WechatMiniProgramBundle\Service\AccountService;
use WechatMiniProgramBundle\Service\Client;
use Yiisoft\Json\Json;

#[MethodDoc(summary: '微信小程序初始化code2session接口')]
#[MethodTag(name: '微信小程序')]
#[MethodExpose(method: 'WechatMiniProgramCodeToSession')]
#[Log]
#[WithMonologChannel(channel: 'procedure')]
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
    #[MethodParam(description: '前端调用 wx.getAccountInfoSync 获得的信息')]
    public string $appId = '';

    #[MethodParam(description: 'wx.login获得的code，如果想模拟指定OpenID登录，可以传入 mock_{"openid":"XXX","unionid":"YYY","session_key":"123"}')]
    #[Assert\NotNull]
    public string $code;

    public function __construct(
        private readonly AccountService $accountService,
        private readonly CodeSessionLogRepository $codeSessionLogRepository,
        private readonly UpsertManager $upsertManager,
        private readonly Client $client,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly UserManagerInterface $userLoader,
        private readonly TokenServiceInterface $accessTokenService,
        private readonly RequestStack $requestStack,
        private readonly LoginService $loginService,
        private readonly Security $security,
        private readonly LockService $lockService,
        private readonly UserTransformService $userTransformService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        // 1. 获取小程序账号配置
        $account = $this->getAccount();

        // 2. 调用微信接口获取 session 信息
        $session = $this->getWechatSession($account);

        // 3. 处理会话日志
        $log = $this->processCodeSessionLog($account, $session);

        // 4. 分发请求事件，允许提前返回
        $event = $this->dispatchCodeToSessionRequestEvent($account, $log);
        if (null !== $event->getReturn()) {
            return $event->getReturn();
        }

        // 5. 构建初始结果
        $result = $this->buildInitialResult($log);

        // 6. 创建或更新微信用户
        $wechatUser = $this->createOrUpdateWechatUser($account, $log);

        // 7. 获取或创建业务用户
        $bizUser = $this->getBizUser($wechatUser, $result);

        // 8. 构建用户相关的返回数据
        $result = $this->buildUserResult($bizUser, $wechatUser, $result);

        // 9. 分发响应事件，允许修改结果
        $result = $this->dispatchCodeToSessionResponseEvent($bizUser, $wechatUser, $log, $result);

        // 10. 最终处理并返回结果
        return $this->finalizeResult($bizUser, $result);
    }

    /**
     * @return array<string>|null
     */
    public function getLockResource(JsonRpcParams $params): ?array
    {
        $code = $params->get('code');
        if (!is_string($code)) {
            throw new ApiException('code参数必须是字符串');
        }

        return [
            'WechatMiniProgramCodeToSession' . $code,
        ];
    }

    protected function getIdempotentCacheKey(JsonRpcRequest $request): string
    {
        $params = $request->getParams();
        if (null === $params) {
            throw new ApiException('请求参数不能为空');
        }

        $code = $params->get('code');
        if (!is_string($code)) {
            throw new ApiException('code参数必须是字符串');
        }

        return 'WechatMiniProgramCodeToSession-idempotent-' . $code;
    }

    private function getAccount(): Account
    {
        $account = $this->accountService->detectAccountFromRequest($this->requestStack->getMainRequest(), $this->appId);
        if (null === $account) {
            throw new ApiException('找不到小程序');
        }

        return $account;
    }

    /**
     * @return array<string, mixed>
     */
    private function getWechatSession(Account $account): array
    {
        $request = new CodeToSessionRequest();
        $request->setAppId($account->getAppId());
        $request->setSecret($account->getAppSecret());
        $request->setJsCode($this->code);

        try {
            $result = $this->client->request($request);
            if (!is_array($result)) {
                throw new ApiException('微信接口返回数据格式错误');
            }

            /** @var array<string, mixed> $result */
            return $result;
        } catch (HttpClientException $exception) {
            if (str_contains($exception->getMessage(), 'invalid code, rid')) {
                throw new ApiException('微信登录态无效，请返回重试', 0, [], $exception);
            }
            throw $exception;
        }
    }

    /**
     * @param array<string, mixed> $session
     */
    private function processCodeSessionLog(Account $account, array $session): CodeSessionLog
    {
        if (!isset($session['session_key'])) {
            return $this->findExistingCodeSessionLog($account);
        }

        return $this->createNewCodeSessionLog($account, $session);
    }

    private function findExistingCodeSessionLog(Account $account): CodeSessionLog
    {
        $log = $this->codeSessionLogRepository->findOneBy([
            'code' => $this->code,
            'account' => $account,
        ]);
        if (null === $log) {
            throw new ApiException('微信登录失败，请重新进入小程序[1]');
        }
        $now = CarbonImmutable::now();
        if (abs($now->diffInSeconds($log->getCreateTime())) > 10) {
            throw new ApiException('微信登录失败，请重新进入小程序[2]');
        }

        return $log;
    }

    /**
     * @param array<string, mixed> $session
     */
    private function createNewCodeSessionLog(Account $account, array $session): CodeSessionLog
    {
        // 验证必需字段
        if (!isset($session['openid']) || !is_string($session['openid'])) {
            throw new ApiException('openid字段不存在或类型不正确');
        }
        if (!isset($session['session_key']) || !is_string($session['session_key'])) {
            throw new ApiException('session_key字段不存在或类型不正确');
        }

        $log = new CodeSessionLog();
        $log->setAccount($account);
        $log->setCode($this->code);
        $log->setOpenId($session['openid']);
        $log->setUnionId(
            isset($session['unionid']) && is_string($session['unionid'])
                ? $session['unionid']
                : ''
        );
        $log->setSessionKey($session['session_key']);
        $log->setRawData(Json::encode($session));
        $log->setLaunchOptions($this->launchOptions);
        $log->setEnterOptions($this->enterOptions);
        $log->setCreatedFromIp($this->requestStack->getMainRequest()?->getClientIp());

        $result = $this->upsertManager->upsert($log);
        \assert($result instanceof CodeSessionLog);

        return $result;
    }

    private function dispatchCodeToSessionRequestEvent(Account $account, CodeSessionLog $log): CodeToSessionRequestEvent
    {
        $event = new CodeToSessionRequestEvent();
        $event->setAccount($account);
        $event->setCode($this->code);
        $event->setCodeSessionLog($log);
        $event->setLaunchOptions($this->launchOptions);
        $event->setEnterOptions($this->enterOptions);
        $event->setBizUser($this->security->getUser());
        $this->eventDispatcher->dispatch($event);

        return $event;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildInitialResult(CodeSessionLog $log): array
    {
        return [
            'sessionKey' => null,
            'openId' => $log->getOpenId(),
            'unionId' => $log->getUnionId(),
        ];
    }

    private function createOrUpdateWechatUser(Account $account, CodeSessionLog $log): User
    {
        $wechatUser = new User();
        $wechatUser->setAccount($account);
        $wechatUser->setLanguage(Language::zh_CN);
        $openId = $log->getOpenId();
        if (null === $openId) {
            throw new ApiException('OpenID不能为空');
        }
        $wechatUser->setOpenId($openId);
        if (null !== $log->getUnionId() && '' !== $log->getUnionId()) {
            $wechatUser->setUnionId($log->getUnionId());
        }
        $wechatUser = $this->upsertManager->upsert($wechatUser);
        \assert($wechatUser instanceof User);

        return $wechatUser;
    }

    /**
     * @param array<string, mixed> $result
     */
    private function getBizUser(User $wechatUser, array $result): UserInterface
    {
        $result = $this->lockService->blockingRun($wechatUser, function () use ($wechatUser, $result): UserInterface {
            // 查找或创建业务用户
            $unionId = isset($result['unionId']) && is_string($result['unionId']) ? $result['unionId'] : null;
            $checkResult = $this->findOrCreateBizUser($wechatUser, $unionId);
            $bizUser = $checkResult['bizUser'];

            // 如果还是没有找到业务用户，则通过 UserTransformService 转换创建
            if (null === $bizUser) {
                return $this->userTransformService->transformToSysUser($wechatUser);
            }

            // 确保返回类型符合 UserInterface
            if (!$bizUser instanceof UserInterface) {
                throw new ApiException('业务用户类型不正确');
            }

            return $bizUser;
        });

        if (!$result instanceof UserInterface) {
            throw new ApiException('lockService返回值类型不正确');
        }

        return $result;
    }

    /**
     * 构建用户相关的返回结果
     *
     * @param array<string, mixed> $result
     *
     * @return array<string, mixed>
     */
    private function buildUserResult(UserInterface $bizUser, User $wechatUser, array $result): array
    {
        // 1. 构建用户基本信息
        if (method_exists($bizUser, 'retrieveApiArray')) {
            $result['user'] = $bizUser->retrieveApiArray();
        } else {
            $result['user'] = [
                'id' => $bizUser->getUserIdentifier(),
                'username' => $bizUser->getUserIdentifier(),
            ];
        }

        // 2. 生成访问令牌
        $token = $this->accessTokenService->createToken($bizUser);
        /** @phpstan-ignore-next-line method.notFound AccessToken implementation provides getToken() method */
        $result['jwt'] = $token->getToken();
        /** @phpstan-ignore-next-line method.notFound AccessToken implementation provides getToken() method */
        $result['access_token'] = $token->getToken();

        // 3. 收集手机号码信息
        $phoneNumbers = [];

        // 从业务用户获取手机号
        if (method_exists($bizUser, 'getMobile') && $bizUser->getMobile()) {
            $phoneNumbers[] = $bizUser->getMobile();
        }

        // 从微信用户获取手机号
        foreach ($wechatUser->getPhoneNumbers() as $phoneNumber) {
            $phoneNumbers[] = $phoneNumber->getPhoneNumber();
        }

        $result['phoneNumbers'] = $phoneNumbers;

        return $result;
    }

    /**
     * 分发响应事件，允许其他监听器修改结果
     *
     * @param array<string, mixed> $result
     *
     * @return array<string, mixed>
     */
    private function dispatchCodeToSessionResponseEvent(UserInterface $bizUser, User $wechatUser, CodeSessionLog $log, array $result): array
    {
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
        $event->setNewUser(false);

        $this->eventDispatcher->dispatch($event);

        // 返回可能被事件监听器修改过的结果
        return $event->getResult();
    }

    /**
     * @param array<string, mixed> $result
     *
     * @return array<string, mixed>
     */
    private function finalizeResult(UserInterface $bizUser, array $result): array
    {
        if (isset($result['phoneNumbers']) && is_array($result['phoneNumbers'])) {
            $result['phoneNumbers'] = array_values(array_unique($result['phoneNumbers']));
        }

        $this->loginService->saveLoginLog($bizUser, 'success');

        return $result;
    }

    /**
     * 查找或创建业务用户
     *
     * @return array{bizUser: ?object, isNewUser: bool}
     */
    private function findOrCreateBizUser(User $wechatUser, ?string $unionId): array
    {
        // 1. 首先尝试通过 openId 查找业务用户
        $bizUser = $this->userLoader->loadUserByIdentifier($wechatUser->getOpenId());

        // 2. 如果没找到，且有 unionId，尝试查找临时用户
        // 这种情况处理通过第三方接口保存的临时用户
        if (null === $bizUser && null !== $unionId && '' !== $unionId) {
            $bizUser = $this->userLoader->loadUserByIdentifier($unionId);
        }

        $isNewUser = (null === $bizUser);

        // 3. 如果找到了业务用户，且有 unionId，更新其身份标识
        if (null !== $bizUser && null !== $wechatUser->getUnionId() && '' !== $wechatUser->getUnionId() && method_exists($bizUser, 'setIdentity')) {
            $bizUser->setIdentity($wechatUser->getUnionId());
            $this->userLoader->saveUser($bizUser);
        }

        return [
            'bizUser' => $bizUser,
            'isNewUser' => $isNewUser,
        ];
    }
}

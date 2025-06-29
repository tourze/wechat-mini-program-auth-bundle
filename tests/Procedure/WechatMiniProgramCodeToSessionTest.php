<?php

namespace WechatMiniProgramAuthBundle\Tests\Procedure;

use AccessTokenBundle\Service\AccessTokenService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\DoctrineUpsertBundle\Service\UpsertManager;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\LoginProtectBundle\Service\LoginService;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Procedure\WechatMiniProgramCodeToSession;
use WechatMiniProgramAuthBundle\Repository\CodeSessionLogRepository;
use WechatMiniProgramAuthBundle\Repository\UserRepository;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Service\AccountService;
use WechatMiniProgramBundle\Service\Client;

class WechatMiniProgramCodeToSessionTest extends TestCase
{
    private $accountService;
    private $codeSessionLogRepository;
    private $entityManager;
    private $upsertManager;
    private $client;
    private $eventDispatcher;
    private $userLoader;
    private $accessTokenService;
    private $requestStack;
    private $loginService;
    private $security;
    private $logger;
    private $userRepository;
    private WechatMiniProgramCodeToSession $procedure;

    protected function setUp(): void
    {
        $this->accountService = $this->createMock(AccountService::class);
        $this->codeSessionLogRepository = $this->createMock(CodeSessionLogRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->upsertManager = $this->createMock(UpsertManager::class);
        $this->client = $this->createMock(Client::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        $this->accessTokenService = $this->createMock(AccessTokenService::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->loginService = $this->createMock(LoginService::class);
        $this->security = $this->createMock(Security::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);

        $this->procedure = new WechatMiniProgramCodeToSession(
            $this->accountService,
            $this->codeSessionLogRepository,
            $this->entityManager,
            $this->upsertManager,
            $this->client,
            $this->eventDispatcher,
            $this->userLoader,
            $this->accessTokenService,
            $this->requestStack,
            $this->loginService,
            $this->security,
            $this->logger,
            $this->userRepository
        );
    }

    public function testExecute_withInvalidAccount()
    {
        // 准备测试数据
        $appId = 'test_app_id';
        $code = 'test_code';
        
        // 设置过程类的属性
        $this->procedure->appId = $appId;
        $this->procedure->code = $code;
        
        // 模拟请求对象
        $request = $this->createMock(Request::class);
        $this->requestStack->method('getMainRequest')->willReturn($request);
        
        // 模拟AccountService返回null，表示找不到小程序
        $this->accountService->method('detectAccountFromRequest')
            ->with($request, $appId)
            ->willReturn(null);
            
        // 预期执行方法会抛出ApiException
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到小程序');
        
        // 执行测试
        $this->procedure->execute();
    }
    
    public function testExecute_withInvalidSessionAndNoPreviousLog()
    {
        // 准备测试数据
        $appId = 'test_app_id';
        $appSecret = 'test_app_secret';
        $code = 'test_code';
        
        // 设置过程类的属性
        $this->procedure->appId = $appId;
        $this->procedure->code = $code;
        
        // 模拟请求对象
        $request = $this->createMock(Request::class);
        $this->requestStack->method('getMainRequest')->willReturn($request);
        
        // 模拟AccountService返回Account对象
        $account = new Account();
        $account->setAppId($appId);
        $account->setAppSecret($appSecret);
        $this->accountService->method('detectAccountFromRequest')
            ->with($request, $appId)
            ->willReturn($account);
            
        // 模拟Client返回无效的会话数据（没有session_key）
        $sessionData = [
            'errcode' => 40029,
            'errmsg' => 'invalid code'
        ];
        $this->client->method('request')
            ->willReturn($sessionData);
            
        // 模拟CodeSessionLogRepository返回null，表示不存在该日志
        $this->codeSessionLogRepository->method('findOneBy')
            ->with(['code' => $code, 'account' => $account])
            ->willReturn(null);
            
        // 预期执行方法会抛出ApiException
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('微信登录失败，请重新进入小程序[1]');
        
        // 执行测试
        $this->procedure->execute();
    }
    
    public function testExecute_withInvalidSessionAndOldPreviousLog()
    {
        // 准备测试数据
        $appId = 'test_app_id';
        $appSecret = 'test_app_secret';
        $code = 'test_code';
        
        // 设置过程类的属性
        $this->procedure->appId = $appId;
        $this->procedure->code = $code;
        
        // 模拟请求对象
        $request = $this->createMock(Request::class);
        $this->requestStack->method('getMainRequest')->willReturn($request);
        
        // 模拟AccountService返回Account对象
        $account = new Account();
        $account->setAppId($appId);
        $account->setAppSecret($appSecret);
        $this->accountService->method('detectAccountFromRequest')
            ->with($request, $appId)
            ->willReturn($account);
            
        // 模拟Client返回无效的会话数据（没有session_key）
        $sessionData = [
            'errcode' => 40029,
            'errmsg' => 'invalid code'
        ];
        $this->client->method('request')
            ->willReturn($sessionData);
            
        // 模拟CodeSessionLogRepository返回很久之前的日志
        $oldLog = new CodeSessionLog();
        $oldLog->setCreateTime(new \DateTimeImmutable('-30 minutes'));
        $this->codeSessionLogRepository->method('findOneBy')
            ->with(['code' => $code, 'account' => $account])
            ->willReturn($oldLog);
            
        // 预期执行方法会抛出ApiException
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('微信登录失败，请重新进入小程序[2]');
        
        // 执行测试
        $this->procedure->execute();
    }
    
    public function testGetLockResource()
    {
        $code = 'test_code';
        $params = new JsonRpcParams(['code' => $code]);
        
        $result = $this->procedure->getLockResource($params);
        
        $this->assertEquals(['WechatMiniProgramCodeToSession' . $code], $result);
    }
} 
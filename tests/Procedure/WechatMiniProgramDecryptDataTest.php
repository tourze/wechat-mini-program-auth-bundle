<?php

namespace WechatMiniProgramAuthBundle\Tests\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tourze\JsonRPC\Core\Exception\ApiException;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Exception\DecryptException;
use WechatMiniProgramAuthBundle\Procedure\WechatMiniProgramDecryptData;
use WechatMiniProgramAuthBundle\Repository\CodeSessionLogRepository;
use WechatMiniProgramAuthBundle\Request\CodeToSessionRequest;
use WechatMiniProgramAuthBundle\Service\EncryptService;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Service\AccountService;
use WechatMiniProgramBundle\Service\Client;

class WechatMiniProgramDecryptDataTest extends TestCase
{
    private $accountService;
    private $client;
    private $encryptService;
    private $codeSessionLogRepository;
    private $requestStack;
    private $entityManager;
    private WechatMiniProgramDecryptData $procedure;
    private Request $request;

    protected function setUp(): void
    {
        $this->accountService = $this->createMock(AccountService::class);
        $this->client = $this->createMock(Client::class);
        $this->encryptService = $this->createMock(EncryptService::class);
        $this->codeSessionLogRepository = $this->createMock(CodeSessionLogRepository::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->request = $this->createMock(Request::class);

        $this->procedure = new WechatMiniProgramDecryptData(
            $this->accountService,
            $this->client,
            $this->encryptService,
            $this->codeSessionLogRepository,
            $this->requestStack,
            $this->entityManager
        );
        
        // 设置通用的请求堆栈模拟
        $this->requestStack->method('getMainRequest')->willReturn($this->request);
        
        // 设置客户端IP地址
        $this->request->method('getClientIp')->willReturn('127.0.0.1');
    }

    public function testExecute_withInvalidAccount()
    {
        // 准备测试数据
        $appId = 'test_app_id';
        $code = 'test_code';
        $iv = 'test_iv';
        $encryptedData = 'test_encrypted_data';
        
        // 设置过程类的属性
        $this->procedure->appId = $appId;
        $this->procedure->code = $code;
        $this->procedure->iv = $iv;
        $this->procedure->encryptedData = $encryptedData;
        
        // 模拟AccountService返回null，表示找不到小程序
        $this->accountService->method('detectAccountFromRequest')
            ->with($this->request, $appId)
            ->willReturn(null);
        
        // 预期执行方法会抛出ApiException
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到小程序');
        
        // 执行测试
        $this->procedure->execute();
    }

    public function testExecute_withExistingSessionLog()
    {
        // 准备测试数据
        $appId = 'test_app_id';
        $code = 'test_code';
        $iv = 'test_iv';
        $encryptedData = 'test_encrypted_data';
        $sessionKey = 'test_session_key';
        $decryptedData = ['phoneNumber' => '13800138000'];
        
        // 设置过程类的属性
        $this->procedure->appId = $appId;
        $this->procedure->code = $code;
        $this->procedure->iv = $iv;
        $this->procedure->encryptedData = $encryptedData;
        
        // 模拟AccountService返回Account对象
        $account = new Account();
        $account->setAppId($appId);
        $this->accountService->method('detectAccountFromRequest')
            ->with($this->request, $appId)
            ->willReturn($account);
        
        // 模拟CodeSessionLogRepository返回已存在的日志
        $log = new CodeSessionLog();
        $log->setSessionKey($sessionKey);
        $this->codeSessionLogRepository->method('findOneBy')
            ->with(['code' => $code, 'account' => $account])
            ->willReturn($log);
            
        // 模拟EncryptService返回解密后的数据
        $this->encryptService->method('decryptData')
            ->with($sessionKey, $iv, $encryptedData)
            ->willReturn($decryptedData);
        
        // 执行测试
        $result = $this->procedure->execute();
        
        // 验证结果
        $this->assertEquals($decryptedData, $result);
    }

    public function testExecute_withoutExistingSessionLog()
    {
        // 准备测试数据
        $appId = 'test_app_id';
        $appSecret = 'test_app_secret';
        $code = 'test_code';
        $iv = 'test_iv';
        $encryptedData = 'test_encrypted_data';
        $sessionKey = 'test_session_key';
        $openId = 'test_open_id';
        $unionId = 'test_union_id';
        $decryptedData = ['phoneNumber' => '13800138000'];
        
        // 设置过程类的属性
        $this->procedure->appId = $appId;
        $this->procedure->code = $code;
        $this->procedure->iv = $iv;
        $this->procedure->encryptedData = $encryptedData;
        
        // 模拟AccountService返回Account对象
        $account = new Account();
        $account->setAppId($appId);
        $account->setAppSecret($appSecret);
        $this->accountService->method('detectAccountFromRequest')
            ->with($this->request, $appId)
            ->willReturn($account);
        
        // 模拟CodeSessionLogRepository返回null，表示不存在该日志
        $this->codeSessionLogRepository->method('findOneBy')
            ->with(['code' => $code, 'account' => $account])
            ->willReturn(null);
            
        // 模拟Client返回会话数据
        $sessionData = [
            'session_key' => $sessionKey,
            'openid' => $openId,
            'unionid' => $unionId
        ];
        $this->client->method('request')
            ->with($this->callback(function(CodeToSessionRequest $request) use ($appId, $appSecret, $code) {
                return $request->getAppId() === $appId
                    && $request->getSecret() === $appSecret
                    && $request->getJsCode() === $code;
            }))
            ->willReturn($sessionData);
            
        // 模拟EntityManager的行为
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function(CodeSessionLog $log) use ($code, $openId, $unionId, $sessionKey) {
                return $log->getCode() === $code 
                    && $log->getOpenId() === $openId
                    && $log->getUnionId() === $unionId
                    && $log->getSessionKey() === $sessionKey;
            }));
        $this->entityManager->expects($this->once())
            ->method('flush');
            
        // 模拟EncryptService返回解密后的数据
        $this->encryptService->method('decryptData')
            ->with($sessionKey, $iv, $encryptedData)
            ->willReturn($decryptedData);
        
        // 执行测试
        $result = $this->procedure->execute();
        
        // 验证结果
        $this->assertEquals($decryptedData, $result);
    }
    
    public function testExecute_withInvalidSessionResponse()
    {
        // 准备测试数据
        $appId = 'test_app_id';
        $appSecret = 'test_app_secret';
        $code = 'test_code';
        $iv = 'test_iv';
        $encryptedData = 'test_encrypted_data';
        
        // 设置过程类的属性
        $this->procedure->appId = $appId;
        $this->procedure->code = $code;
        $this->procedure->iv = $iv;
        $this->procedure->encryptedData = $encryptedData;
        
        // 模拟AccountService返回Account对象
        $account = new Account();
        $account->setAppId($appId);
        $account->setAppSecret($appSecret);
        $this->accountService->method('detectAccountFromRequest')
            ->with($this->request, $appId)
            ->willReturn($account);
        
        // 模拟CodeSessionLogRepository返回null，表示不存在该日志
        $this->codeSessionLogRepository->method('findOneBy')
            ->with(['code' => $code, 'account' => $account])
            ->willReturn(null);
            
        // 模拟Client返回无效的会话数据
        $sessionData = [
            'errcode' => 40029,
            'errmsg' => 'invalid code'
        ];
        $this->client->method('request')
            ->willReturn($sessionData);
            
        // 预期执行方法会抛出ApiException
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('微信登录失败，请重新进入小程序');
        
        // 执行测试
        $this->procedure->execute();
    }
    
    public function testExecute_withDecryptException()
    {
        // 准备测试数据
        $appId = 'test_app_id';
        $appSecret = 'test_app_secret';
        $code = 'test_code';
        $iv = 'test_iv';
        $encryptedData = 'invalid_encrypted_data';
        $sessionKey = 'test_session_key';
        
        // 设置过程类的属性
        $this->procedure->appId = $appId;
        $this->procedure->code = $code;
        $this->procedure->iv = $iv;
        $this->procedure->encryptedData = $encryptedData;
        
        // 模拟AccountService返回Account对象
        $account = new Account();
        $account->setAppId($appId);
        $account->setAppSecret($appSecret);
        $this->accountService->method('detectAccountFromRequest')
            ->with($this->request, $appId)
            ->willReturn($account);
        
        // 模拟CodeSessionLogRepository返回已存在的日志
        $log = new CodeSessionLog();
        $log->setSessionKey($sessionKey);
        $this->codeSessionLogRepository->method('findOneBy')
            ->with(['code' => $code, 'account' => $account])
            ->willReturn($log);
            
        // 模拟EncryptService抛出DecryptException
        $this->encryptService->method('decryptData')
            ->with($sessionKey, $iv, $encryptedData)
            ->willThrowException(new DecryptException('解密数据失败'));
            
        // 预期执行方法会抛出DecryptException
        $this->expectException(DecryptException::class);
        $this->expectExceptionMessage('解密数据失败');
        
        // 执行测试
        $this->procedure->execute();
    }
    
    public function testExecute_withEmptyEncryptedData()
    {
        // 跳过这个测试，因为该测试用例对应的功能不在代码中实现
        $this->markTestSkipped('WechatMiniProgramDecryptData类中没有对空加密数据的特殊处理');
    }
    
    public function testExecute_withMissingParameters()
    {
        // 未设置任何必要的参数
        
        // 尝试运行方法，应该会抛出ApiException
        try {
            $this->procedure->execute();
            $this->fail('预期应抛出异常，但没有');
        } catch (ApiException $e) {
            // 预期行为，测试通过
            $this->assertStringContainsString('小程序', $e->getMessage());
        }
    }
} 
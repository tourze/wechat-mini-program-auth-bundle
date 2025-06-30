<?php

namespace WechatMiniProgramAuthBundle\Tests\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tourze\JsonRPC\Core\Exception\ApiException;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Procedure\UpdateWechatMiniProgramProfile;
use WechatMiniProgramAuthBundle\Repository\CodeSessionLogRepository;
use WechatMiniProgramAuthBundle\Repository\UserRepository;
use WechatMiniProgramAuthBundle\Service\EncryptService;
use WechatMiniProgramBundle\Service\AccountService;
use WechatMiniProgramBundle\Service\Client;

class UpdateWechatMiniProgramProfileTest extends TestCase
{
    private CodeSessionLogRepository $codeSessionLogRepository;
    private UserRepository $userRepository;
    private EncryptService $encryptService;
    private AccountService $accountService;
    private Client $client;
    private LoggerInterface $logger;
    private RequestStack $requestStack;
    private EntityManagerInterface $entityManager;
    private UpdateWechatMiniProgramProfile $procedure;

    protected function setUp(): void
    {
        $this->codeSessionLogRepository = $this->createMock(CodeSessionLogRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->encryptService = $this->createMock(EncryptService::class);
        $this->accountService = $this->createMock(AccountService::class);
        $this->client = $this->createMock(Client::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->procedure = new UpdateWechatMiniProgramProfile(
            $this->accountService,
            $this->client,
            $this->encryptService,
            $this->codeSessionLogRepository,
            $this->requestStack,
            $this->logger,
            $this->userRepository,
            $this->entityManager
        );
    }

    public function testExecuteWithoutRequest(): void
    {
        $this->requestStack->expects(self::once())
            ->method('getMainRequest')
            ->willReturn(null);

        $this->accountService->expects(self::once())
            ->method('detectAccountFromRequest')
            ->with(null, '')
            ->willReturn(null);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到小程序');

        $this->procedure->execute();
    }

    public function testExecuteCodeSessionLogNotFound(): void
    {
        $request = new Request();
        $this->requestStack->expects(self::once())
            ->method('getMainRequest')
            ->willReturn($request);

        $account = $this->createMock(\WechatMiniProgramBundle\Entity\Account::class);
        $this->accountService->expects(self::once())
            ->method('detectAccountFromRequest')
            ->with($request, '')
            ->willReturn($account);

        $this->procedure->code = 'test_code';

        $this->codeSessionLogRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['code' => 'test_code', 'account' => $account])
            ->willReturn(null);

        $codeToSessionRequest = $this->createMock(\WechatMiniProgramAuthBundle\Request\CodeToSessionRequest::class);
        $this->client->expects(self::once())
            ->method('request')
            ->willReturn([]); // 返回无效响应来触发异常

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('微信登录失败，请重新进入小程序');

        $this->procedure->execute();
    }

    public function testExecuteWithInvalidAppId(): void
    {
        $request = new Request();
        $this->requestStack->expects(self::once())
            ->method('getMainRequest')
            ->willReturn($request);

        $this->procedure->appId = 'invalid_app_id';

        $this->accountService->expects(self::once())
            ->method('detectAccountFromRequest')
            ->with($request, 'invalid_app_id')
            ->willReturn(null);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到小程序');

        $this->procedure->execute();
    }
}
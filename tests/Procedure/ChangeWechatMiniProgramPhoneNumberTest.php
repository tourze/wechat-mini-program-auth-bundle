<?php

namespace WechatMiniProgramAuthBundle\Tests\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Exception\DecryptException;
use WechatMiniProgramAuthBundle\Procedure\ChangeWechatMiniProgramPhoneNumber;
use WechatMiniProgramAuthBundle\Repository\PhoneNumberRepository;
use WechatMiniProgramAuthBundle\Service\EncryptService;

class ChangeWechatMiniProgramPhoneNumberTest extends TestCase
{
    private UserLoaderInterface $userLoader;
    private PhoneNumberRepository $phoneNumberRepository;
    private EncryptService $encryptService;
    private EventDispatcherInterface $eventDispatcher;
    private Security $security;
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;
    private ChangeWechatMiniProgramPhoneNumber $procedure;

    protected function setUp(): void
    {
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        $this->phoneNumberRepository = $this->createMock(PhoneNumberRepository::class);
        $this->encryptService = $this->createMock(EncryptService::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->procedure = new ChangeWechatMiniProgramPhoneNumber(
            $this->userLoader,
            $this->phoneNumberRepository,
            $this->encryptService,
            $this->eventDispatcher,
            $this->security,
            $this->logger,
            $this->entityManager
        );
    }

    public function testExecuteUserNotFound(): void
    {
        $bizUser = $this->createMock(UserInterface::class);
        $bizUser->expects(self::once())
            ->method('getUserIdentifier')
            ->willReturn('test_user_id');

        $this->security->expects(self::once())
            ->method('getUser')
            ->willReturn($bizUser);

        $this->userLoader->expects(self::once())
            ->method('loadUserByOpenId')
            ->with('test_user_id')
            ->willReturn(null);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到微信小程序用户信息');

        $this->procedure->execute();
    }

    public function testExecuteDecryptFailed(): void
    {
        $bizUser = $this->createMock(UserInterface::class);
        $bizUser->expects(self::once())
            ->method('getUserIdentifier')
            ->willReturn('test_user_id');

        $this->security->expects(self::once())
            ->method('getUser')
            ->willReturn($bizUser);

        $wechatUser = new User();
        $this->userLoader->expects(self::once())
            ->method('loadUserByOpenId')
            ->with('test_user_id')
            ->willReturn($wechatUser);

        $this->procedure->sessionKey = 'test_session_key';
        $this->procedure->iv = 'test_iv';
        $this->procedure->encryptedData = 'test_encrypted_data';

        $decryptException = new DecryptException('解密失败');
        $this->encryptService->expects(self::once())
            ->method('decryptData')
            ->with('test_session_key', 'test_iv', 'test_encrypted_data')
            ->willThrowException($decryptException);

        $this->logger->expects(self::once())
            ->method('error')
            ->with('旧方式解密手机失败', self::isType('array'));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到手机号码，请重试');

        $this->procedure->execute();
    }
}
<?php

namespace WechatMiniProgramAuthBundle\Tests\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Procedure\UploadWechatMiniProgramPhoneNumber;
use WechatMiniProgramAuthBundle\Repository\PhoneNumberRepository;
use WechatMiniProgramBundle\Service\Client;

class UploadWechatMiniProgramPhoneNumberTest extends TestCase
{
    private Client $client;
    private UserLoaderInterface $userLoader;
    private PhoneNumberRepository $phoneNumberRepository;
    private EventDispatcherInterface $eventDispatcher;
    private Security $security;
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;
    private UploadWechatMiniProgramPhoneNumber $procedure;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        $this->phoneNumberRepository = $this->createMock(PhoneNumberRepository::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->procedure = new UploadWechatMiniProgramPhoneNumber(
            $this->userLoader,
            $this->phoneNumberRepository,
            $this->client,
            $this->eventDispatcher,
            $this->security,
            $this->logger,
            $this->entityManager
        );
    }

    public function testExecuteUserNotFound(): void
    {
        $this->procedure->code = 'test_code';

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

    public function testExecuteWithInvalidMiniProgram(): void
    {
        $this->procedure->code = 'test_code';

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

        $wechatUser->setAccount(null);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('该用户没有绑定微信小程序');

        $this->procedure->execute();
    }
}
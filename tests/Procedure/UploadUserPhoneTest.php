<?php

namespace WechatMiniProgramAuthBundle\Tests\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Event\GetPhoneNumberEvent;
use WechatMiniProgramAuthBundle\Procedure\UploadUserPhone;
use WechatMiniProgramAuthBundle\Repository\PhoneNumberRepository;
use WechatMiniProgramAuthBundle\Repository\UserRepository;

class UploadUserPhoneTest extends TestCase
{
    private UserRepository $userRepository;
    private PhoneNumberRepository $phoneNumberRepository;
    private Security $security;
    private EventDispatcherInterface $eventDispatcher;
    private EntityManagerInterface $entityManager;
    private UploadUserPhone $procedure;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->phoneNumberRepository = $this->createMock(PhoneNumberRepository::class);
        $this->security = $this->createMock(Security::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->procedure = new UploadUserPhone(
            $this->userRepository,
            $this->phoneNumberRepository,
            $this->security,
            $this->eventDispatcher,
            $this->entityManager
        );
    }

    public function testExecuteUserNotFound(): void
    {
        $bizUser = $this->createMock(UserInterface::class);
        $this->security->expects(self::once())
            ->method('getUser')
            ->willReturn($bizUser);
        
        $this->userRepository->expects(self::once())
            ->method('getBySysUser')
            ->with($bizUser)
            ->willReturn(null);
        
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到微信小程序用户信息');
        
        $this->procedure->execute();
    }

    public function testExecuteEmptyPhoneNumber(): void
    {
        $bizUser = $this->createMock(UserInterface::class);
        $this->security->expects(self::once())
            ->method('getUser')
            ->willReturn($bizUser);
        
        $user = new User();
        $this->userRepository->expects(self::once())
            ->method('getBySysUser')
            ->with($bizUser)
            ->willReturn($user);
        
        $this->procedure->phoneNumber = '';
        
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('请求参数不正确');
        
        $this->procedure->execute();
    }

    public function testExecuteSuccess(): void
    {
        $bizUser = $this->createMock(UserInterface::class);
        $this->security->expects(self::exactly(2))
            ->method('getUser')
            ->willReturn($bizUser);
        
        $user = new User();
        $this->userRepository->expects(self::once())
            ->method('getBySysUser')
            ->with($bizUser)
            ->willReturn($user);
        
        $this->procedure->phoneNumber = '13800138000';
        
        $this->phoneNumberRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['phoneNumber' => '13800138000'])
            ->willReturn(null);
        
        $this->eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(GetPhoneNumberEvent::class));
        
        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(PhoneNumber::class));
        $this->entityManager->expects(self::once())
            ->method('flush');
        
        $result = $this->procedure->execute();
        
        self::assertSame(['message' => '更新成功'], $result);
    }
}
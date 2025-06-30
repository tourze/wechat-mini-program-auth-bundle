<?php

namespace WechatMiniProgramAuthBundle\Tests\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;
use WechatMiniProgramAuthBundle\Procedure\ReportWechatMiniProgramAuthorizeResult;

class ReportWechatMiniProgramAuthorizeResultTest extends TestCase
{
    private UserLoaderInterface $userLoader;
    private EntityManagerInterface $entityManager;
    private Security $security;
    private ReportWechatMiniProgramAuthorizeResult $procedure;

    protected function setUp(): void
    {
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        
        $this->procedure = new ReportWechatMiniProgramAuthorizeResult(
            $this->userLoader,
            $this->entityManager,
            $this->security
        );
    }

    public function testExecuteSuccess(): void
    {
        $this->procedure->scopes = ['scope.userInfo', 'scope.userLocation'];
        
        $bizUser = $this->createMock(\Symfony\Component\Security\Core\User\UserInterface::class);
        $bizUser->expects(self::once())
            ->method('getUserIdentifier')
            ->willReturn('test_open_id');
        
        $this->security->expects(self::once())
            ->method('getUser')
            ->willReturn($bizUser);
        
        $user = $this->createMock(\WechatMiniProgramAuthBundle\Entity\User::class);
        $user->expects(self::once())
            ->method('setAuthorizeScopes')
            ->with(['scope.userInfo', 'scope.userLocation']);
        $user->expects(self::once())
            ->method('getId')
            ->willReturn(123);
        
        $this->userLoader->expects(self::once())
            ->method('loadUserByOpenId')
            ->with('test_open_id')
            ->willReturn($user);
        
        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with($user);
        $this->entityManager->expects(self::once())
            ->method('flush');
        
        $result = $this->procedure->execute();
        
        self::assertSame(['id' => 123], $result);
    }
    
    public function testExecuteUserNotFound(): void
    {
        $this->procedure->scopes = [];
        
        $bizUser = $this->createMock(\Symfony\Component\Security\Core\User\UserInterface::class);
        $bizUser->expects(self::once())
            ->method('getUserIdentifier')
            ->willReturn('test_open_id');
        
        $this->security->expects(self::once())
            ->method('getUser')
            ->willReturn($bizUser);
        
        $this->userLoader->expects(self::once())
            ->method('loadUserByOpenId')
            ->with('test_open_id')
            ->willReturn(null);
        
        $this->expectException(\Tourze\JsonRPC\Core\Exception\ApiException::class);
        $this->expectExceptionMessage('找不到微信小程序用户信息');
        
        $this->procedure->execute();
    }
    
    public function testGetMockResult(): void
    {
        $result = ReportWechatMiniProgramAuthorizeResult::getMockResult();
        
        self::assertSame(['id' => 456], $result);
    }
}
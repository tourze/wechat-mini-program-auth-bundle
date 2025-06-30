<?php

namespace WechatMiniProgramAuthBundle\Tests\Procedure;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Procedure\GetCurrentWechatMiniProgramUser;
use WechatMiniProgramAuthBundle\Repository\UserRepository;

class GetCurrentWechatMiniProgramUserTest extends TestCase
{
    private UserRepository $userRepository;
    private Security $security;
    private GetCurrentWechatMiniProgramUser $procedure;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->security = $this->createMock(Security::class);
        
        $this->procedure = new GetCurrentWechatMiniProgramUser(
            $this->userRepository,
            $this->security
        );
    }

    public function testExecuteSuccess(): void
    {
        $sysUser = $this->createMock(UserInterface::class);
        $this->security->expects(self::once())
            ->method('getUser')
            ->willReturn($sysUser);

        $wechatUser = new User();
        $wechatUser->setOpenId('test_open_id');
        $wechatUser->setUnionId('test_union_id');

        $this->userRepository->expects(self::once())
            ->method('getBySysUser')
            ->with($sysUser)
            ->willReturn($wechatUser);

        $result = $this->procedure->execute();

        self::assertSame([
            'open_id' => 'test_open_id',
            'union_id' => 'test_union_id',
        ], $result);
    }

    public function testExecuteUserNotFound(): void
    {
        $sysUser = $this->createMock(UserInterface::class);
        $this->security->expects(self::once())
            ->method('getUser')
            ->willReturn($sysUser);

        $this->userRepository->expects(self::once())
            ->method('getBySysUser')
            ->with($sysUser)
            ->willReturn(null);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到微信小程序用户信息');

        $this->procedure->execute();
    }
}
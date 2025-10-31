<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Procedure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Procedure\GetCurrentWechatMiniProgramUser;

/**
 * @internal
 */
#[CoversClass(GetCurrentWechatMiniProgramUser::class)]
#[RunTestsInSeparateProcesses]
final class GetCurrentWechatMiniProgramUserTest extends AbstractProcedureTestCase
{
    private GetCurrentWechatMiniProgramUser $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(GetCurrentWechatMiniProgramUser::class);
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(GetCurrentWechatMiniProgramUser::class, $this->procedure);
    }

    public function testExecuteWithUnauthenticatedUser(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('用户未登录');

        $this->procedure->execute();
    }

    public function testExecuteWithAuthenticatedUser(): void
    {
        // 创建系统用户
        $sysUser = $this->createAdminUser('test-wechat-user');

        // 设置认证用户
        $this->setAuthenticatedUser($sysUser);

        // 创建微信小程序用户
        $wechatUser = new User();
        $wechatUser->setOpenId('test-open-id-123');
        $wechatUser->setUnionId('test-union-id-123');
        $wechatUser->setNickName('Test User');
        $wechatUser->setUser($sysUser);
        $this->persistAndFlush($wechatUser);

        $result = $this->procedure->execute();

        $this->assertEquals('test-open-id-123', $result['open_id']);
        $this->assertEquals('test-union-id-123', $result['union_id']);
    }

    public function testExecuteWithUserNotFound(): void
    {
        // 创建系统用户但不关联微信小程序用户
        $sysUser = $this->createAdminUser('test-no-wechat-user');

        // 设置认证用户
        $this->setAuthenticatedUser($sysUser);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到微信小程序用户信息');

        $this->procedure->execute();
    }
}

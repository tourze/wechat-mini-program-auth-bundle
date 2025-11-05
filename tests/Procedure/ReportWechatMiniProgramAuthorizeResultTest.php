<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Procedure\ReportWechatMiniProgramAuthorizeResult;

/**
 * @internal
 */
#[CoversClass(ReportWechatMiniProgramAuthorizeResult::class)]
#[RunTestsInSeparateProcesses]
final class ReportWechatMiniProgramAuthorizeResultTest extends AbstractProcedureTestCase
{
    private ReportWechatMiniProgramAuthorizeResult $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(ReportWechatMiniProgramAuthorizeResult::class);
    }

    public function testGetMockResult(): void
    {
        $result = ReportWechatMiniProgramAuthorizeResult::getMockResult();

        self::assertSame(['id' => 456], $result);
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
        $sysUser = $this->createAdminUser('test-authorize-user');

        // 设置认证用户
        $this->setAuthenticatedUser($sysUser);

        // 创建微信小程序用户
        $wechatUser = new User();
        $wechatUser->setOpenId('test-open-id-456');
        $wechatUser->setUnionId('test-union-id-456');
        $wechatUser->setNickName('Test Authorize User');
        $wechatUser->setUser($sysUser);
        $this->persistAndFlush($wechatUser);

        // 设置授权scopes
        $this->procedure->scopes = ['scope.userInfo', 'scope.userLocation'];

        $result = $this->procedure->execute();

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($wechatUser->getId(), $result['id']);

        // 验证scopes已被保存
        self::getService(EntityManagerInterface::class)->refresh($wechatUser);
        $this->assertEquals(['scope.userInfo', 'scope.userLocation'], $wechatUser->getAuthorizeScopes());
    }

    public function testExecuteWithUserNotFound(): void
    {
        // 创建系统用户但不关联微信小程序用户
        $sysUser = $this->createAdminUser('test-no-authorize-user');

        // 设置认证用户
        $this->setAuthenticatedUser($sysUser);

        $this->procedure->scopes = ['scope.userInfo'];

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到微信小程序用户信息');

        $this->procedure->execute();
    }
}

<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Procedure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DependencyInjection\Container;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Procedure\UploadWechatMiniProgramPhoneNumber;
use WechatMiniProgramAuthBundle\Tests\Service\TestUserLoader;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Service\Client;

/**
 * @internal
 */
#[CoversClass(UploadWechatMiniProgramPhoneNumber::class)]
#[RunTestsInSeparateProcesses]
final class UploadWechatMiniProgramPhoneNumberTest extends AbstractProcedureTestCase
{
    private UploadWechatMiniProgramPhoneNumber $procedure;

    protected function onSetUp(): void
    {
        // 在服务初始化之前，我们不获取 procedure，留到各个测试方法中按需获取
    }

    public function testGenerateFormattedLogText(): void
    {
        $this->procedure = self::getService(UploadWechatMiniProgramPhoneNumber::class);
        $reflection = new \ReflectionClass(JsonRpcRequest::class);
        $request = $reflection->newInstanceWithoutConstructor();
        $result = $this->procedure->generateFormattedLogText($request);
        $this->assertEquals('授权微信小程序手机号码', $result);
    }

    public function testExecuteWithUnauthenticatedUser(): void
    {
        $this->procedure = self::getService(UploadWechatMiniProgramPhoneNumber::class);
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('用户未登录');

        $this->procedure->execute();
    }

    public function testExecuteWithAuthenticatedUser(): void
    {
        // Mock WeChat client 在服务初始化之前
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('request')
            ->willReturn([
                'phone_info' => [
                    'phoneNumber' => '13800138000',
                    'purePhoneNumber' => '13800138000',
                    'countryCode' => '86',
                    'watermark' => [
                        'timestamp' => time(),
                        'appid' => 'test-app-id',
                    ],
                ],
            ])
        ;

        // 在服务初始化之前设置 Mock
        /** @var Container $container */
        /** @phpstan-ignore-next-line */
        $container = $this->getContainer();
        $container->set(Client::class, $mockClient);

        // 现在获取 procedure 服务（此时会使用我们的 Mock Client）
        $this->procedure = self::getService(UploadWechatMiniProgramPhoneNumber::class);

        // 设置必要的参数
        $this->procedure->code = 'test-code-123';

        // 创建系统用户，用户标识符需要与 wechat 用户的 openId 匹配
        $openId = 'test-open-id-789';
        $sysUser = $this->createAdminUser($openId);

        // 设置认证用户
        $this->setAuthenticatedUser($sysUser);

        // 创建微信小程序账户
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test-app-id');
        $account->setAppSecret('test-app-secret');
        $account->setValid(true);
        $this->persistAndFlush($account);

        // 创建微信小程序用户
        $wechatUser = new User();
        $wechatUser->setOpenId($openId);
        $wechatUser->setUnionId('test-union-id-789');
        $wechatUser->setNickName('Test Phone User');
        $wechatUser->setUser($sysUser);
        $wechatUser->setAccount($account);
        $this->persistAndFlush($wechatUser);

        // 将用户添加到 TestUserLoader 中，以便 userLoader 能找到用户
        $testUserLoader = self::getService(TestUserLoader::class);
        $this->assertInstanceOf(TestUserLoader::class, $testUserLoader);
        $testUserLoader->addUser($wechatUser);

        $result = $this->procedure->execute();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('phoneNumber', $result);
        $this->assertEquals('13800138000', $result['phoneNumber']);
    }

    public function testExecuteWithUserNotFound(): void
    {
        $this->procedure = self::getService(UploadWechatMiniProgramPhoneNumber::class);

        // 创建系统用户但不关联微信小程序用户
        $sysUser = $this->createAdminUser('test-no-phone-user');

        // 设置认证用户
        $this->setAuthenticatedUser($sysUser);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到微信小程序用户信息');

        $this->procedure->execute();
    }
}

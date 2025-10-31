<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramAuthBundle\Service\UserService;

/**
 * @internal
 */
#[CoversClass(UserService::class)]
#[RunTestsInSeparateProcesses]
final class UserServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 测试不需要额外的设置
    }

    public function testServiceClassExists(): void
    {
        self::assertTrue(class_exists(UserService::class));
    }

    public function testCreateUserMethodSignature(): void
    {
        $reflectionClass = new \ReflectionClass(UserService::class);
        $method = $reflectionClass->getMethod('createUser');

        self::assertTrue($method->isPublic());

        $parameters = $method->getParameters();
        self::assertCount(3, $parameters);

        self::assertEquals('miniProgram', $parameters[0]->getName());
        self::assertEquals('openId', $parameters[1]->getName());
        self::assertEquals('unionId', $parameters[2]->getName());

        self::assertTrue($parameters[2]->isOptional());
        self::assertNull($parameters[2]->getDefaultValue());
    }

    public function testConstructorRequiresDependencies(): void
    {
        $reflectionClass = new \ReflectionClass(UserService::class);
        $constructor = $reflectionClass->getConstructor();

        self::assertNotNull($constructor);
        $parameters = $constructor->getParameters();

        // 验证构造函数需要依赖注入
        self::assertGreaterThan(0, count($parameters));
    }
}

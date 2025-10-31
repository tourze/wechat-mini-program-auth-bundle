<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatMiniProgramAuthBundle\Service\UserTransformService;

/**
 * @internal
 */
#[CoversClass(UserTransformService::class)]
final class UserTransformServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 测试不需要额外的设置
    }

    public function testServiceClassExists(): void
    {
        self::assertTrue(class_exists(UserTransformService::class));
    }

    public function testConstructorRequiresDependencies(): void
    {
        $reflectionClass = new \ReflectionClass(UserTransformService::class);
        $constructor = $reflectionClass->getConstructor();

        self::assertNotNull($constructor);
        $parameters = $constructor->getParameters();

        // 验证构造函数需要依赖注入
        self::assertCount(2, $parameters);
        self::assertEquals('userLoader', $parameters[0]->getName());
        self::assertEquals('userRepository', $parameters[1]->getName());
    }

    public function testTransformToSysUserMethodExists(): void
    {
        $reflectionClass = new \ReflectionClass(UserTransformService::class);
        self::assertTrue($reflectionClass->hasMethod('transformToSysUser'));

        $method = $reflectionClass->getMethod('transformToSysUser');
        self::assertTrue($method->isPublic());

        $parameters = $method->getParameters();
        self::assertCount(1, $parameters);
        self::assertEquals('entity', $parameters[0]->getName());
    }

    public function testTransformToWechatUserMethodExists(): void
    {
        $reflectionClass = new \ReflectionClass(UserTransformService::class);
        self::assertTrue($reflectionClass->hasMethod('transformToWechatUser'));

        $method = $reflectionClass->getMethod('transformToWechatUser');
        self::assertTrue($method->isPublic());

        $parameters = $method->getParameters();
        self::assertCount(1, $parameters);
        self::assertEquals('user', $parameters[0]->getName());
    }
}

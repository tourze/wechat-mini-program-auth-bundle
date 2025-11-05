<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatMiniProgramAuthBundle\Service\EncryptService;

/**
 * @internal
 */
#[CoversClass(EncryptService::class)]
final class EncryptServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 测试不需要额外的设置
    }

    public function testDecryptDataInterfaceContract(): void
    {
        $service = new EncryptService();

        // 验证方法签名和返回类型
        $reflectionMethod = new \ReflectionMethod(EncryptService::class, 'decryptData');
        $parameters = $reflectionMethod->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('sessionKey', $parameters[0]->getName());
        $this->assertEquals('iv', $parameters[1]->getName());
        $this->assertEquals('encrypted', $parameters[2]->getName());

        // 验证返回类型
        $returnType = $reflectionMethod->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
    }

    public function testDecryptDataWithInvalidBase64Input(): void
    {
        $service = new EncryptService();

        // 测试使用无效的base64输入时会抛出异常
        $this->expectException(\Exception::class);

        // 使用无效的base64编码数据
        $service->decryptData('invalid_session_key', 'invalid_iv', 'invalid_encrypted_data');
    }

    public function testEncryptServiceInstantiation(): void
    {
        $service = new EncryptService();
        $this->assertSame(EncryptService::class, $service::class);
    }

    public function testDecryptDataParameterTypes(): void
    {
        $reflectionMethod = new \ReflectionMethod(EncryptService::class, 'decryptData');
        $parameters = $reflectionMethod->getParameters();

        $this->assertTrue($parameters[0]->hasType());
        $this->assertEquals('string', (string) $parameters[0]->getType());

        $this->assertTrue($parameters[1]->hasType());
        $this->assertEquals('string', (string) $parameters[1]->getType());

        $this->assertTrue($parameters[2]->hasType());
        $this->assertEquals('string', (string) $parameters[2]->getType());
    }
}

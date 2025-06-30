<?php

namespace WechatMiniProgramAuthBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramAuthBundle\Exception\DecryptException;
use WechatMiniProgramAuthBundle\Service\EncryptService;

class EncryptServiceTest extends TestCase
{
    public function testDecryptDataInterfaceContract()
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

    public function testDecryptDataWithInvalidBase64Input()
    {
        $service = new EncryptService();
        
        // 测试使用无效的base64输入时会抛出异常
        $this->expectException(\Exception::class);
        
        // 使用无效的base64编码数据
        $service->decryptData('invalid_session_key', 'invalid_iv', 'invalid_encrypted_data');
    }


    public function testEncryptServiceInstantiation()
    {
        $service = new EncryptService();
        $this->assertInstanceOf(EncryptService::class, $service);
    }

    public function testDecryptDataParameterTypes()
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
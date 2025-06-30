<?php

namespace WechatMiniProgramAuthBundle\Tests\Procedure;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\ApiException;
use WechatMiniProgramAuthBundle\Procedure\GetUserInfoByPhone;
use WechatMiniProgramAuthBundle\Repository\UserRepository;

class GetUserInfoByPhoneTest extends TestCase
{
    private UserRepository $userRepository;
    private GetUserInfoByPhone $procedure;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->procedure = new GetUserInfoByPhone($this->userRepository);
    }

    public function testExecuteEmptyPhoneNumber(): void
    {
        $this->procedure->phoneNumber = '';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('请求参数不正确');

        $this->procedure->execute();
    }

    public function testProcedureInstantiation(): void
    {
        self::assertInstanceOf(GetUserInfoByPhone::class, $this->procedure);
        self::assertInstanceOf(UserRepository::class, $this->userRepository);
    }

    public function testProcedureHasPhoneNumberProperty(): void
    {
        $reflectionClass = new \ReflectionClass(GetUserInfoByPhone::class);
        self::assertTrue($reflectionClass->hasProperty('phoneNumber'));
        
        $property = $reflectionClass->getProperty('phoneNumber');
        self::assertTrue($property->isPublic());
    }

}
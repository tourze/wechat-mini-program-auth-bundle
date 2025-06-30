<?php

namespace WechatMiniProgramAuthBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WechatMiniProgramAuthBundle\Exception\UserManagerNotAvailableException;

class UserManagerNotAvailableExceptionTest extends TestCase
{
    public function testExceptionInheritance()
    {
        $exception = new UserManagerNotAvailableException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testExceptionWithMessage()
    {
        $message = 'UserManager is not available';
        $exception = new UserManagerNotAvailableException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode()
    {
        $message = 'UserManager is not available';
        $code = 500;
        $exception = new UserManagerNotAvailableException($message, $code);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testExceptionWithPrevious()
    {
        $previous = new \Exception('Previous exception');
        $exception = new UserManagerNotAvailableException('Test message', 0, $previous);
        $this->assertEquals($previous, $exception->getPrevious());
    }

    public function testExceptionCanBeThrown()
    {
        $this->expectException(UserManagerNotAvailableException::class);
        $this->expectExceptionMessage('UserManager not available');
        
        throw new UserManagerNotAvailableException('UserManager not available');
    }
}
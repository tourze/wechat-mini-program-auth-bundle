<?php

namespace WechatMiniProgramAuthBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramAuthBundle\Exception\DecryptException;

class DecryptExceptionTest extends TestCase
{
    public function testExceptionInheritance()
    {
        $exception = new DecryptException();
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testExceptionWithMessage()
    {
        $message = 'Test decrypt exception message';
        $exception = new DecryptException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode()
    {
        $message = 'Test decrypt exception message';
        $code = 500;
        $exception = new DecryptException($message, $code);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testExceptionWithPrevious()
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new DecryptException('Test message', 0, $previous);
        $this->assertEquals($previous, $exception->getPrevious());
    }

    public function testExceptionCanBeThrown()
    {
        $this->expectException(DecryptException::class);
        $this->expectExceptionMessage('Test exception');
        
        throw new DecryptException('Test exception');
    }
}
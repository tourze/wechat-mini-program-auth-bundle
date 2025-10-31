<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use WechatMiniProgramAuthBundle\Exception\DecryptException;

/**
 * @internal
 */
#[CoversClass(DecryptException::class)]
final class DecryptExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 测试不需要额外的设置
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test decrypt exception message';
        $exception = new DecryptException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode(): void
    {
        $message = 'Test decrypt exception message';
        $code = 500;
        $exception = new DecryptException($message, $code);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testExceptionWithPrevious(): void
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new DecryptException('Test message', 0, $previous);
        $this->assertEquals($previous, $exception->getPrevious());
    }

    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(DecryptException::class);
        $this->expectExceptionMessage('Test exception');

        throw new DecryptException('Test exception');
    }
}

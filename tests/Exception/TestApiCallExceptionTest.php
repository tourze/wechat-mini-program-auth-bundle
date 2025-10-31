<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use WechatMiniProgramAuthBundle\Exception\TestApiCallException;

/**
 * @internal
 */
#[CoversClass(TestApiCallException::class)]
final class TestApiCallExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeCreated(): void
    {
        $exception = new TestApiCallException('Test message');

        $this->assertInstanceOf(TestApiCallException::class, $exception);
        $this->assertSame('Test message', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionWithCode(): void
    {
        $exception = new TestApiCallException('Test message', 500);

        $this->assertSame(500, $exception->getCode());
    }

    public function testExceptionWithPrevious(): void
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new TestApiCallException('Test message', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}

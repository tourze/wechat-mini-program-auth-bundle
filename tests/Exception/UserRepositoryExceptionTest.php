<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use WechatMiniProgramAuthBundle\Exception\UserRepositoryException;

/**
 * @internal
 */
#[CoversClass(UserRepositoryException::class)]
final class UserRepositoryExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionInheritance(): void
    {
        $exception = new UserRepositoryException('Test message');

        self::assertSame('Test message', $exception->getMessage());
        self::assertSame(\RuntimeException::class, get_parent_class($exception));
    }

    public function testExceptionWithCode(): void
    {
        $exception = new UserRepositoryException('Test message', 123);

        self::assertSame('Test message', $exception->getMessage());
        self::assertSame(123, $exception->getCode());
    }

    public function testExceptionWithPreviousException(): void
    {
        $previousException = new \Exception('Previous exception');
        $exception = new UserRepositoryException('Test message', 0, $previousException);

        self::assertSame('Test message', $exception->getMessage());
        self::assertSame($previousException, $exception->getPrevious());
    }
}

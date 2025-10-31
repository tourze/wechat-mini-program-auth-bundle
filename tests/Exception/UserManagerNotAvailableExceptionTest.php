<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use WechatMiniProgramAuthBundle\Exception\UserManagerNotAvailableException;

/**
 * @internal
 */
#[CoversClass(UserManagerNotAvailableException::class)]
final class UserManagerNotAvailableExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 测试不需要额外的设置
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'UserManager is not available';
        $exception = new UserManagerNotAvailableException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode(): void
    {
        $message = 'UserManager is not available';
        $code = 500;
        $exception = new UserManagerNotAvailableException($message, $code);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testExceptionWithPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new UserManagerNotAvailableException('Test message', 0, $previous);
        $this->assertEquals($previous, $exception->getPrevious());
    }

    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(UserManagerNotAvailableException::class);
        $this->expectExceptionMessage('UserManager not available');

        throw new UserManagerNotAvailableException('UserManager not available');
    }
}

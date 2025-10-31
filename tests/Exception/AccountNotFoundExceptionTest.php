<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use WechatMiniProgramAuthBundle\Exception\AccountNotFoundException;

/**
 * @internal
 */
#[CoversClass(AccountNotFoundException::class)]
final class AccountNotFoundExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 测试不需要额外的设置
    }

    public function testExceptionExtendsRuntimeException(): void
    {
        $exception = new AccountNotFoundException();
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testDefaultMessage(): void
    {
        $exception = new AccountNotFoundException();
        $this->assertEquals('Account is required but not found', $exception->getMessage());
    }

    public function testCustomMessage(): void
    {
        $customMessage = 'Custom error message';
        $exception = new AccountNotFoundException($customMessage);
        $this->assertEquals($customMessage, $exception->getMessage());
    }

    public function testCustomCodeAndPrevious(): void
    {
        $code = 404;
        $previous = new \Exception('Previous exception');
        $exception = new AccountNotFoundException('Test message', $code, $previous);

        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}

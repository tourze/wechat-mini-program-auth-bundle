<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use WechatMiniProgramAuthBundle\Exception\SystemUserNotFoundException;

/**
 * @internal
 */
#[CoversClass(SystemUserNotFoundException::class)]
final class SystemUserNotFoundExceptionTest extends AbstractExceptionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 测试不需要额外的设置
    }

    public function testExceptionMessage(): void
    {
        $exception = new SystemUserNotFoundException();

        $this->assertEquals('没有找到对应的系统用户', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function testCustomMessage(): void
    {
        $message = '自定义错误消息';
        $code = 404;

        $exception = new SystemUserNotFoundException($message, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}

<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Exception;

use RuntimeException;

/**
 * 测试异常类，用于禁止在测试中调用真实API
 */
class TestApiCallException extends \RuntimeException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

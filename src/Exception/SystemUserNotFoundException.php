<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Exception;

/**
 * 系统用户未找到异常
 */
class SystemUserNotFoundException extends \RuntimeException
{
    public function __construct(string $message = '没有找到对应的系统用户', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

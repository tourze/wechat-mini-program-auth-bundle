<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Exception;

class AccountNotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Account is required but not found', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

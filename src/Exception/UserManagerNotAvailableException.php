<?php

namespace WechatMiniProgramAuthBundle\Exception;

use RuntimeException;

/**
 * 当 UserLoader 不实现 UserManagerInterface 而无法创建新用户时抛出
 */
class UserManagerNotAvailableException extends RuntimeException
{
}
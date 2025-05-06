<?php

namespace WechatMiniProgramAuthBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 微信的性别枚举
 */
enum Gender: int implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case UNKNOWN = 0;
    case MALE = 1;
    case FEMALE = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::UNKNOWN => '未知',
            self::MALE => '男性',
            self::FEMALE => '女性',
        };
    }
}

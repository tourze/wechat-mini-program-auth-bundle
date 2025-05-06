<?php

namespace WechatMiniProgramAuthBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 显示 country，province，city 所用的语言。强制返回 “zh_CN”
 */
enum Language: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case en = 'en';
    case zh_CN = 'zh_CN';
    case zh_TW = 'zh_TW';

    public function getLabel(): string
    {
        return match ($this) {
            self::en => 'en',
            self::zh_CN => 'zh_CN',
            self::zh_TW => 'zh_TW',
        };
    }
}

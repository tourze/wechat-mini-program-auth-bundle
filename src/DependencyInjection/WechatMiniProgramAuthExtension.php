<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

#[Autoconfigure(public: true)]
class WechatMiniProgramAuthExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}

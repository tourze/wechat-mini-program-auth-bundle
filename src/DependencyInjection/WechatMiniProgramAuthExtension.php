<?php

namespace WechatMiniProgramAuthBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Tourze\DoctrineResolveTargetEntityBundle\DependencyInjection\Compiler\ResolveTargetEntityPass;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
use WechatMiniProgramAuthBundle\Entity\User;

class WechatMiniProgramAuthExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');

        $container->addCompilerPass(
            new ResolveTargetEntityPass(UserInterface::class, User::class),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            1000,
        );
    }
}

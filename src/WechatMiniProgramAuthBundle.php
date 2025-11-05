<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use HttpClientBundle\HttpClientBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\AccessTokenBundle\AccessTokenBundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineIpBundle\DoctrineIpBundle;
use Tourze\DoctrineResolveTargetEntityBundle\DependencyInjection\Compiler\ResolveTargetEntityPass;
use Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle;
use Tourze\DoctrineUpsertBundle\DoctrineUpsertBundle;
use Tourze\DoctrineUserBundle\DoctrineUserBundle;
use Tourze\JsonRPCLockBundle\JsonRPCLockBundle;
use Tourze\JsonRPCLogBundle\JsonRPCLogBundle;
use Tourze\JsonRPCSecurityBundle\JsonRPCSecurityBundle;
use Tourze\LoginProtectBundle\LoginProtectBundle;
use Tourze\TextManageBundle\TextManageBundle;
use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\WechatMiniProgramBundle;
use Tourze\EasyAdminMenuBundle\EasyAdminMenuBundle;

class WechatMiniProgramAuthBundle extends Bundle implements BundleDependencyInterface
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(
            new ResolveTargetEntityPass(UserInterface::class, User::class),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            1000,
        );

        $container->addCompilerPass(
            new ResolveTargetEntityPass(MiniProgramInterface::class, Account::class),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            1000,
        );
    }

    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            DoctrineIpBundle::class => ['all' => true],
            DoctrineTimestampBundle::class => ['all' => true],
            DoctrineUpsertBundle::class => ['all' => true],
            DoctrineUserBundle::class => ['all' => true],
            TextManageBundle::class => ['all' => true],
            HttpClientBundle::class => ['all' => true],
            JsonRPCLockBundle::class => ['all' => true],
            JsonRPCLogBundle::class => ['all' => true],
            LoginProtectBundle::class => ['all' => true],
            SecurityBundle::class => ['all' => true],
            WechatMiniProgramBundle::class => ['all' => true],
            JsonRPCSecurityBundle::class => ['all' => true],
            EasyAdminMenuBundle::class => ['all' => true],
            AccessTokenBundle::class => ['all' => true],
        ];
    }
}

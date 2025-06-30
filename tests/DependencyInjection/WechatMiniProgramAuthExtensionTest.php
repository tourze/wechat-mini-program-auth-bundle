<?php

namespace WechatMiniProgramAuthBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WechatMiniProgramAuthBundle\DependencyInjection\WechatMiniProgramAuthExtension;

class WechatMiniProgramAuthExtensionTest extends TestCase
{
    private ContainerBuilder $container;
    private WechatMiniProgramAuthExtension $extension;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->extension = new WechatMiniProgramAuthExtension();
    }

    public function testLoad(): void
    {
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasDefinition('WechatMiniProgramAuthBundle\Service\EncryptService'));
        self::assertTrue($this->container->hasDefinition('WechatMiniProgramAuthBundle\Service\AdminMenu'));
        self::assertTrue($this->container->hasDefinition('WechatMiniProgramAuthBundle\Service\WechatTextFormatter'));
    }

    public function testServicesAreLoaded(): void
    {
        $this->extension->load([], $this->container);
        
        self::assertTrue($this->container->hasDefinition('WechatMiniProgramAuthBundle\Service\EncryptService'));
        self::assertTrue($this->container->hasDefinition('WechatMiniProgramAuthBundle\Service\AdminMenu'));
        self::assertTrue($this->container->hasDefinition('WechatMiniProgramAuthBundle\Service\WechatTextFormatter'));
    }
}
<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use WechatMiniProgramAuthBundle\DependencyInjection\WechatMiniProgramAuthExtension;

/**
 * @internal
 */
#[CoversClass(WechatMiniProgramAuthExtension::class)]
final class WechatMiniProgramAuthExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private ContainerBuilder $container;

    private WechatMiniProgramAuthExtension $extension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new ContainerBuilder();
        $this->extension = new WechatMiniProgramAuthExtension();
    }

    public function testServicesAreLoaded(): void
    {
        $this->container->setParameter('kernel.environment', 'test');
        $this->extension->load([], $this->container);

        self::assertTrue($this->container->hasDefinition('WechatMiniProgramAuthBundle\Service\EncryptService'));
        self::assertTrue($this->container->hasDefinition('WechatMiniProgramAuthBundle\Service\AdminMenu'));
        self::assertTrue($this->container->hasDefinition('WechatMiniProgramAuthBundle\Service\WechatTextFormatter'));
    }
}

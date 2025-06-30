<?php

namespace WechatMiniProgramAuthBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WechatMiniProgramAuthBundle\WechatMiniProgramAuthBundle;

class WechatMiniProgramAuthBundleTest extends TestCase
{
    public function testBundleInheritance()
    {
        $bundle = new WechatMiniProgramAuthBundle();
        $this->assertInstanceOf(Bundle::class, $bundle);
    }

    public function testBundleInstantiation()
    {
        $bundle = new WechatMiniProgramAuthBundle();
        $this->assertNotNull($bundle);
        $this->assertInstanceOf(WechatMiniProgramAuthBundle::class, $bundle);
    }

    public function testBundleName()
    {
        $bundle = new WechatMiniProgramAuthBundle();
        $this->assertEquals('WechatMiniProgramAuthBundle', $bundle->getName());
    }

    public function testBundleNamespace()
    {
        $bundle = new WechatMiniProgramAuthBundle();
        $this->assertEquals('WechatMiniProgramAuthBundle', $bundle->getNamespace());
    }

    public function testBundlePath()
    {
        $bundle = new WechatMiniProgramAuthBundle();
        $path = $bundle->getPath();
        $this->assertStringContainsString('wechat-mini-program-auth-bundle', $path);
        $this->assertStringEndsWith('src', $path);
    }
}
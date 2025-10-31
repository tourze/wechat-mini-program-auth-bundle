<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramAuthBundle\Service\WechatTextFormatter;

/**
 * @internal
 */
#[CoversClass(WechatTextFormatter::class)]
#[RunTestsInSeparateProcesses]
final class WechatTextFormatterTest extends AbstractIntegrationTestCase
{
    private WechatTextFormatter $formatter;

    protected function onSetUp(): void
    {
        $this->formatter = self::getService(WechatTextFormatter::class);
    }

    public function testFormatTextWithNoWechatUser(): void
    {
        $text = 'Hello {name}';
        $params = ['name' => 'World'];

        $result = $this->formatter->formatText($text, $params);

        $this->assertStringContainsString('Hello', $result);
        $this->assertInstanceOf(WechatTextFormatter::class, $this->formatter);
    }

    public function testFormatTextBasicFunctionality(): void
    {
        $text = 'Hello {name}';
        $params = ['name' => 'World'];

        $result = $this->formatter->formatText($text, $params);

        $this->assertIsString($result);
        $this->assertStringContainsString('Hello', $result);
    }

    public function testFormatTextWithWechatPlaceholders(): void
    {
        $text = 'OpenId: {wechatMiniProgram:openId}, UnionId: {wechatMiniProgram:unionId}';
        $params = [];

        $result = $this->formatter->formatText($text, $params);

        $this->assertIsString($result);
        $this->assertInstanceOf(WechatTextFormatter::class, $this->formatter);
    }

    public function testServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(WechatTextFormatter::class, $this->formatter);
    }
}

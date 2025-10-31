<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use WechatMiniProgramAuthBundle\WechatMiniProgramAuthBundle;

/**
 * @internal
 */
#[CoversClass(WechatMiniProgramAuthBundle::class)]
#[RunTestsInSeparateProcesses]
final class WechatMiniProgramAuthBundleTest extends AbstractBundleTestCase
{
}

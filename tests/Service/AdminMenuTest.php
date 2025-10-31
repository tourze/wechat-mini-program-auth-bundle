<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use WechatMiniProgramAuthBundle\Service\AdminMenu;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    protected function onSetUp(): void
    {
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    public function testInvokeAddsMenuItems(): void
    {
        $item = $this->createMock(ItemInterface::class);
        $wechatChild = $this->createMock(ItemInterface::class);
        $customerChild = $this->createMock(ItemInterface::class);

        $item->expects(self::exactly(4))
            ->method('getChild')
            ->willReturnCallback($this->createGetChildCallback($wechatChild, $customerChild))
        ;

        $item->expects(self::exactly(2))
            ->method('addChild')
            ->willReturnCallback($this->createAddChildCallback($wechatChild, $customerChild))
        ;

        $wechatChild->expects(self::exactly(3))
            ->method('addChild')
            ->willReturnCallback(function ($name) {
                $child = $this->createMock(ItemInterface::class);
                $child->expects(self::once())->method('setUri');

                return $child;
            })
        ;

        $customerChild->expects(self::once())
            ->method('addChild')
            ->with('小程序会员')
            ->willReturnCallback(function () {
                $child = $this->createMock(ItemInterface::class);
                $child->expects(self::once())->method('setUri');

                return $child;
            })
        ;

        $this->adminMenu->__invoke($item);
        $this->assertInstanceOf(AdminMenu::class, $this->adminMenu);
    }

    private function createGetChildCallback(ItemInterface $wechatChild, ItemInterface $customerChild): \Closure
    {
        return function ($name) use ($wechatChild, $customerChild) {
            if ('微信小程序' === $name) {
                /** @var int $wechatCallCount */
                static $wechatCallCount = 0;

                $currentCount = $wechatCallCount;
                ++$wechatCallCount;

                return 0 === $currentCount ? null : $wechatChild;
            }
            if ('客户管理' === $name) {
                /** @var int $customerCallCount */
                static $customerCallCount = 0;

                $currentCount = $customerCallCount;
                ++$customerCallCount;

                return 0 === $currentCount ? null : $customerChild;
            }

            return null;
        };
    }

    private function createAddChildCallback(ItemInterface $wechatChild, ItemInterface $customerChild): \Closure
    {
        return function ($name, $options = []) use ($wechatChild, $customerChild) {
            return match ($name) {
                '微信小程序' => $wechatChild,
                '客户管理' => $customerChild,
                default => $this->createMock(ItemInterface::class),
            };
        };
    }
}

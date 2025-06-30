<?php

namespace WechatMiniProgramAuthBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Service\AdminMenu;

class AdminMenuTest extends TestCase
{
    private LinkGeneratorInterface $linkGenerator;
    private AdminMenu $adminMenu;

    protected function setUp(): void
    {
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $this->adminMenu = new AdminMenu($this->linkGenerator);
    }

    public function testInvokeAddsMenuItems(): void
    {
        $item = $this->createMock(ItemInterface::class);
        
        $wechatChild = $this->createMock(ItemInterface::class);
        $customerChild = $this->createMock(ItemInterface::class);
        
        // 微信小程序菜单
        $item->expects(self::exactly(4))
            ->method('getChild')
            ->willReturnCallback(function ($name) use ($wechatChild, $customerChild) {
                static $callCount = 0;
                $callCount++;
                
                if ($callCount === 1 && $name === '微信小程序') {
                    return null;
                } elseif ($callCount === 2 && $name === '微信小程序') {
                    return $wechatChild;
                } elseif ($callCount === 3 && $name === '客户管理') {
                    return null;
                } elseif ($callCount === 4 && $name === '客户管理') {
                    return $customerChild;
                }
                return null;
            });
        
        $item->expects(self::exactly(2))
            ->method('addChild')
            ->willReturnCallback(function ($name, $options = []) use ($wechatChild, $customerChild) {
                if ($name === '微信小程序') {
                    return $wechatChild;
                } elseif ($name === '客户管理') {
                    return $customerChild;
                }
                return $this->createMock(ItemInterface::class);
            });
        
        $codeSessionLogUrl = '/admin/code-session-log';
        $this->linkGenerator->expects(self::exactly(2))
            ->method('getCurdListPage')
            ->willReturnCallback(function ($entityClass) use ($codeSessionLogUrl) {
                if ($entityClass === CodeSessionLog::class) {
                    return $codeSessionLogUrl;
                } elseif ($entityClass === User::class) {
                    return '/admin/user';
                }
                return '';
            });
        
        $codeSessionLogChild = $this->createMock(ItemInterface::class);
        $codeSessionLogChild->expects(self::once())
            ->method('setUri')
            ->with($codeSessionLogUrl);
        
        $wechatChild->expects(self::once())
            ->method('addChild')
            ->with('code2session日志')
            ->willReturn($codeSessionLogChild);
        
        $userChild = $this->createMock(ItemInterface::class);
        $userChild->expects(self::once())
            ->method('setUri')
            ->with('/admin/user');
        
        $customerChild->expects(self::once())
            ->method('addChild')
            ->with('小程序会员')
            ->willReturn($userChild);
        
        $this->adminMenu->__invoke($item);
    }
}
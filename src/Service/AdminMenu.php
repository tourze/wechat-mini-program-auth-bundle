<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use WechatMiniProgramAuthBundle\Entity\AuthLog;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramAuthBundle\Entity\User;

readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('微信小程序')) {
            $item->addChild('微信小程序', [
                'attributes' => [
                    'icon' => 'icon icon-wechat',
                ],
            ]);
        }

        $wechatMenu = $item->getChild('微信小程序');
        if (null !== $wechatMenu) {
            $wechatMenu->addChild('授权日志')->setUri($this->linkGenerator->getCurdListPage(AuthLog::class));
            $wechatMenu->addChild('code2session日志')->setUri($this->linkGenerator->getCurdListPage(CodeSessionLog::class));
            $wechatMenu->addChild('授权手机号')->setUri($this->linkGenerator->getCurdListPage(PhoneNumber::class));
        }

        if (null === $item->getChild('客户管理')) {
            $item->addChild('客户管理');
        }
        $customerMenu = $item->getChild('客户管理');
        if (null !== $customerMenu) {
            $customerMenu->addChild('小程序会员')->setUri($this->linkGenerator->getCurdListPage(User::class));
        }
    }
}

<?php

namespace WechatMiniProgramAuthBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Entity\User;

class AdminMenu implements MenuProviderInterface
{
    public function __construct(private readonly LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if ($item->getChild('微信小程序') === null) {
            $item->addChild('微信小程序', [
                'attributes' => [
                    'icon' => 'icon icon-wechat',
                ],
            ]);
        }
        $item->getChild('微信小程序')->addChild('code2session日志')->setUri($this->linkGenerator->getCurdListPage(CodeSessionLog::class));

        if ($item->getChild('客户管理') === null) {
            $item->addChild('客户管理');
        }
        $item->getChild('客户管理')->addChild('小程序会员')->setUri($this->linkGenerator->getCurdListPage(User::class));

    }
}

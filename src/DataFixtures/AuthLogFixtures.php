<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use WechatMiniProgramAuthBundle\Entity\AuthLog;

#[When(env: 'test')]
#[When(env: 'dev')]
class AuthLogFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $authLog = new AuthLog();
        $authLog->setOpenId('openid_test_123456');
        $authLog->setRawData('{"openid":"openid_test_123456","nickname":"测试用户","sex":1,"language":"zh_CN","city":"深圳","province":"广东","country":"中国","headimgurl":"https://thispersondoesnotexist.com/avatar1.jpg"}');
        $manager->persist($authLog);

        $authLog = new AuthLog();
        $authLog->setOpenId('openid_test_789012');
        $authLog->setRawData('{"openid":"openid_test_789012","nickname":"示例用户","sex":2,"language":"zh_CN","city":"北京","province":"北京","country":"中国","headimgurl":"https://thispersondoesnotexist.com/avatar2.jpg"}');
        $manager->persist($authLog);

        $manager->flush();
    }
}

<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Enum\Gender;
use WechatMiniProgramAuthBundle\Enum\Language;

#[When(env: 'test')]
#[When(env: 'dev')]
class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setOpenId('openid_test_123456');
        $user->setUnionId('unionid_test_123456');
        $user->setNickName('测试用户');
        $user->setAvatarUrl('https://thispersondoesnotexist.com/avatar1.jpg');
        $user->setGender(Gender::MALE);
        $user->setCountry('中国');
        $user->setProvince('广东');
        $user->setCity('深圳');
        $user->setLanguage(Language::zh_CN);
        $user->setRawData('{"openId":"openid_test_123456","nickName":"测试用户","gender":1,"language":"zh_CN","city":"深圳","province":"广东","country":"中国","avatarUrl":"https://thispersondoesnotexist.com/avatar1.jpg"}');
        $user->setAuthorizeScopes(['scope.userInfo', 'scope.userLocation']);
        $manager->persist($user);

        $user = new User();
        $user->setOpenId('openid_test_789012');
        $user->setNickName('示例用户');
        $user->setAvatarUrl('https://thispersondoesnotexist.com/avatar2.jpg');
        $user->setGender(Gender::FEMALE);
        $user->setCountry('中国');
        $user->setProvince('北京');
        $user->setCity('北京');
        $user->setLanguage(Language::zh_CN);
        $user->setRawData('{"openId":"openid_test_789012","nickName":"示例用户","gender":2,"language":"zh_CN","city":"北京","province":"北京","country":"中国","avatarUrl":"https://thispersondoesnotexist.com/avatar2.jpg"}');
        $user->setAuthorizeScopes(['scope.userInfo']);
        $manager->persist($user);

        $manager->flush();
    }
}

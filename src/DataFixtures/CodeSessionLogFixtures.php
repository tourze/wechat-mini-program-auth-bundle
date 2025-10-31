<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;

#[When(env: 'test')]
#[When(env: 'dev')]
class CodeSessionLogFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $codeSessionLog = new CodeSessionLog();
        $codeSessionLog->setCode('code_test_123456');
        $codeSessionLog->setOpenId('openid_test_123456');
        $codeSessionLog->setUnionId('unionid_test_123456');
        $codeSessionLog->setSessionKey('session_key_test_123456');
        $codeSessionLog->setRawData('{"openid":"openid_test_123456","session_key":"session_key_test_123456","unionid":"unionid_test_123456"}');
        $manager->persist($codeSessionLog);

        $codeSessionLog = new CodeSessionLog();
        $codeSessionLog->setCode('code_test_789012');
        $codeSessionLog->setOpenId('openid_test_789012');
        $codeSessionLog->setSessionKey('session_key_test_789012');
        $codeSessionLog->setRawData('{"openid":"openid_test_789012","session_key":"session_key_test_789012"}');
        $manager->persist($codeSessionLog);

        $manager->flush();
    }
}

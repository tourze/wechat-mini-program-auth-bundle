<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;

#[When(env: 'test')]
#[When(env: 'dev')]
class PhoneNumberFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $phoneNumber = new PhoneNumber();
        $phoneNumber->setPhoneNumber('+8613812345678');
        $phoneNumber->setPurePhoneNumber('13812345678');
        $phoneNumber->setCountryCode('+86');
        $phoneNumber->setWatermark([
            'timestamp' => 1640995200,
            'appid' => 'wx123456789',
        ]);
        $phoneNumber->setRawData('{"phoneNumber":"+8613812345678","purePhoneNumber":"13812345678","countryCode":"+86","watermark":{"timestamp":1640995200,"appid":"wx123456789"}}');
        $manager->persist($phoneNumber);

        $phoneNumber = new PhoneNumber();
        $phoneNumber->setPhoneNumber('+8613987654321');
        $phoneNumber->setPurePhoneNumber('13987654321');
        $phoneNumber->setCountryCode('+86');
        $phoneNumber->setWatermark([
            'timestamp' => 1640995300,
            'appid' => 'wx987654321',
        ]);
        $phoneNumber->setRawData('{"phoneNumber":"+8613987654321","purePhoneNumber":"13987654321","countryCode":"+86","watermark":{"timestamp":1640995300,"appid":"wx987654321"}}');
        $manager->persist($phoneNumber);

        $manager->flush();
    }
}

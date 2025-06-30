<?php

namespace WechatMiniProgramAuthBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Event\GetPhoneNumberEvent;

class GetPhoneNumberEventTest extends TestCase
{
    public function testEventProperties(): void
    {
        $event = new GetPhoneNumberEvent();
        
        $phoneNumber = new PhoneNumber();
        $event->setPhoneNumber($phoneNumber);
        self::assertSame($phoneNumber, $event->getPhoneNumber());
        
        $wechatUser = new User();
        $event->setWechatUser($wechatUser);
        self::assertSame($wechatUser, $event->getWechatUser());
        
        $result = ['phone' => '13800138000'];
        $event->setResult($result);
        self::assertSame($result, $event->getResult());
    }

    public function testInheritedProperties(): void
    {
        $event = new GetPhoneNumberEvent();
        
        $sender = $this->createMock(UserInterface::class);
        $event->setSender($sender);
        self::assertSame($sender, $event->getSender());
        
        $receiver = $this->createMock(UserInterface::class);
        $event->setReceiver($receiver);
        self::assertSame($receiver, $event->getReceiver());
        
        $message = '获取手机号码';
        $event->setMessage($message);
        self::assertSame($message, $event->getMessage());
    }
}
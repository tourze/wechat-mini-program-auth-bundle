<?php

namespace WechatMiniProgramAuthBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Event\ChangePhoneNumberEvent;

class ChangePhoneNumberEventTest extends TestCase
{
    public function testEventProperties(): void
    {
        $event = new ChangePhoneNumberEvent();
        
        $phoneNumber = new PhoneNumber();
        $event->setPhoneNumber($phoneNumber);
        self::assertSame($phoneNumber, $event->getPhoneNumber());
        
        $wechatUser = new User();
        $event->setWechatUser($wechatUser);
        self::assertSame($wechatUser, $event->getWechatUser());
        
        $result = ['success' => true];
        $event->setResult($result);
        self::assertSame($result, $event->getResult());
    }

    public function testSetterAndGetter(): void
    {
        $event = new ChangePhoneNumberEvent();
        
        $sender = $this->createMock(UserInterface::class);
        $event->setSender($sender);
        self::assertSame($sender, $event->getSender());
        
        $receiver = $this->createMock(UserInterface::class);
        $event->setReceiver($receiver);
        self::assertSame($receiver, $event->getReceiver());
        
        $message = '修改手机号码';
        $event->setMessage($message);
        self::assertSame($message, $event->getMessage());
    }
}
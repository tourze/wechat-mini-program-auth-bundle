<?php

namespace WechatMiniProgramAuthBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramAuthBundle\Entity\User;

class PhoneNumberTest extends TestCase
{
    private PhoneNumber $phoneNumber;

    protected function setUp(): void
    {
        $this->phoneNumber = new PhoneNumber();
    }

    public function testSetAndGetPhoneNumber()
    {
        $phone = '+8613800138000';
        $this->phoneNumber->setPhoneNumber($phone);
        $this->assertEquals($phone, $this->phoneNumber->getPhoneNumber());
    }

    public function testSetAndGetPurePhoneNumber()
    {
        $purePhone = '13800138000';
        $this->phoneNumber->setPurePhoneNumber($purePhone);
        $this->assertEquals($purePhone, $this->phoneNumber->getPurePhoneNumber());
    }

    public function testSetAndGetCountryCode()
    {
        $countryCode = '86';
        $this->phoneNumber->setCountryCode($countryCode);
        $this->assertEquals($countryCode, $this->phoneNumber->getCountryCode());
    }

    public function testSetAndGetRawData()
    {
        $rawData = '{"phoneNumber":"+8613800138000","purePhoneNumber":"13800138000","countryCode":"86"}';
        $this->phoneNumber->setRawData($rawData);
        $this->assertEquals($rawData, $this->phoneNumber->getRawData());
    }

    public function testAddAndGetUsers()
    {
        $user = $this->createMock(User::class);
        $this->phoneNumber->addUser($user);
        
        $users = $this->phoneNumber->getUsers();
        $this->assertCount(1, $users);
        $this->assertSame($user, $users->first());
    }
    
    public function testRemoveUser()
    {
        $user = $this->createMock(User::class);
        $this->phoneNumber->addUser($user);
        $this->phoneNumber->removeUser($user);
        
        $users = $this->phoneNumber->getUsers();
        $this->assertCount(0, $users);
    }

    public function testSetAndGetWatermark()
    {
        $watermark = [
            'timestamp' => 1623123456,
            'appid' => 'wx12345678'
        ];
        $this->phoneNumber->setWatermark($watermark);
        $this->assertEquals($watermark, $this->phoneNumber->getWatermark());
    }

    public function testTimestampFields()
    {
        $now = new \DateTimeImmutable();
        $this->phoneNumber->setCreateTime($now);
        $this->phoneNumber->setUpdateTime($now);
        
        $this->assertEquals($now, $this->phoneNumber->getCreateTime());
        $this->assertEquals($now, $this->phoneNumber->getUpdateTime());
    }
} 
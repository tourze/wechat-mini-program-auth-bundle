<?php

namespace WechatMiniProgramAuthBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Enum\Gender;
use WechatMiniProgramAuthBundle\Enum\Language;
use WechatMiniProgramBundle\Entity\Account;

class UserTest extends TestCase
{
    private User $user;
    private Account $account;

    protected function setUp(): void
    {
        $this->user = new User();
        $this->account = $this->createMock(Account::class);
    }

    public function testSetAndGetOpenId()
    {
        $openId = 'test_open_id';
        $this->user->setOpenId($openId);
        $this->assertEquals($openId, $this->user->getOpenId());
    }

    public function testSetAndGetUnionId()
    {
        $unionId = 'test_union_id';
        $this->user->setUnionId($unionId);
        $this->assertEquals($unionId, $this->user->getUnionId());
    }

    public function testSetAndGetNickName()
    {
        $nickName = 'test_nick_name';
        $this->user->setNickName($nickName);
        $this->assertEquals($nickName, $this->user->getNickName());
    }

    public function testSetAndGetAvatarUrl()
    {
        $avatarUrl = 'https://example.com/avatar.jpg';
        $this->user->setAvatarUrl($avatarUrl);
        $this->assertEquals($avatarUrl, $this->user->getAvatarUrl());
    }

    public function testSetAndGetGender()
    {
        $gender = Gender::MALE;
        $this->user->setGender($gender);
        $this->assertEquals($gender, $this->user->getGender());
    }

    public function testSetAndGetCountry()
    {
        $country = 'China';
        $this->user->setCountry($country);
        $this->assertEquals($country, $this->user->getCountry());
    }

    public function testSetAndGetProvince()
    {
        $province = 'Guangdong';
        $this->user->setProvince($province);
        $this->assertEquals($province, $this->user->getProvince());
    }

    public function testSetAndGetCity()
    {
        $city = 'Shenzhen';
        $this->user->setCity($city);
        $this->assertEquals($city, $this->user->getCity());
    }

    public function testSetAndGetLanguage()
    {
        $language = Language::zh_CN;
        $this->user->setLanguage($language);
        $this->assertEquals($language, $this->user->getLanguage());
    }

    public function testSetAndGetAccount()
    {
        $this->user->setAccount($this->account);
        $this->assertSame($this->account, $this->user->getAccount());
    }

    public function testToString()
    {
        // 设置ID为空
        $reflectionClass = new ReflectionClass(User::class);
        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->user, null);
        
        // 当ID为null时，应该返回空字符串
        $this->assertEquals('', (string)$this->user);
        
        // 设置ID和OpenId
        $idProperty->setValue($this->user, 1);
        $openId = 'test_open_id';
        $this->user->setOpenId($openId);
        
        // 设置一个空昵称
        $this->user->setNickName('');
        
        // 当昵称为空字符串时，应返回openId
        $this->assertEquals($openId, (string)$this->user);
        
        // 设置昵称
        $nickName = 'test_user';
        $this->user->setNickName($nickName);
        
        // 当有昵称时，应返回"昵称(openId)"格式
        $this->assertEquals("{$nickName}({$openId})", (string)$this->user);
    }
    
    public function testGetIdentityValue()
    {
        $openId = 'test_open_id';
        $this->user->setOpenId($openId);
        $this->assertEquals($openId, $this->user->getIdentityValue());
    }
    
    public function testGetIdentityType()
    {
        $appId = 'wx12345678';
        $this->account->method('getAppId')->willReturn($appId);
        $this->user->setAccount($this->account);
        
        $expectedType = User::IDENTITY_PREFIX . $appId;
        $this->assertEquals($expectedType, $this->user->getIdentityType());
    }
    
    public function testAddAndGetPhoneNumbers()
    {
        $phoneNumber = $this->createMock(\WechatMiniProgramAuthBundle\Entity\PhoneNumber::class);
        $this->user->addPhoneNumber($phoneNumber);
        
        $phoneNumbers = $this->user->getPhoneNumbers();
        $this->assertCount(1, $phoneNumbers);
        $this->assertSame($phoneNumber, $phoneNumbers->first());
    }
    
    public function testRemovePhoneNumber()
    {
        $phoneNumber = $this->createMock(\WechatMiniProgramAuthBundle\Entity\PhoneNumber::class);
        $this->user->addPhoneNumber($phoneNumber);
        $this->user->removePhoneNumber($phoneNumber);
        
        $phoneNumbers = $this->user->getPhoneNumbers();
        $this->assertCount(0, $phoneNumbers);
    }
} 
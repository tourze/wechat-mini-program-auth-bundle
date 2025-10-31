<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Enum\Gender;
use WechatMiniProgramAuthBundle\Enum\Language;

/**
 * @internal
 */
#[CoversClass(User::class)]
final class UserTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new User();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'openId' => ['openId', 'test_value'],
            'unionId' => ['unionId', 'test_value'],
            'nickName' => ['nickName', 'test_value'],
            'avatarUrl' => ['avatarUrl', 'test_value'],
            'country' => ['country', 'test_value'],
            'province' => ['province', 'test_value'],
            'city' => ['city', 'test_value'],
        ];
    }

    private User $user;

    private MockObject|MiniProgramInterface $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new User();
        // 使用 MiniProgramInterface 接口的 mock，理由如下：
        // 1. User 实体的 setAccount 方法期望 MiniProgramInterface 类型
        // 2. 测试需要验证 User 与 MiniProgramInterface 的关联关系
        // 3. 通过接口 mock 可以避免对具体实现的依赖
        $this->account = $this->createMock(MiniProgramInterface::class);
    }

    public function testSetAndGetOpenId(): void
    {
        $openId = 'test_open_id';
        $this->user->setOpenId($openId);
        $this->assertEquals($openId, $this->user->getOpenId());
    }

    public function testSetAndGetUnionId(): void
    {
        $unionId = 'test_union_id';
        $this->user->setUnionId($unionId);
        $this->assertEquals($unionId, $this->user->getUnionId());
    }

    public function testSetAndGetNickName(): void
    {
        $nickName = 'test_nick_name';
        $this->user->setNickName($nickName);
        $this->assertEquals($nickName, $this->user->getNickName());
    }

    public function testSetAndGetAvatarUrl(): void
    {
        $avatarUrl = 'https://example.com/avatar.jpg';
        $this->user->setAvatarUrl($avatarUrl);
        $this->assertEquals($avatarUrl, $this->user->getAvatarUrl());
    }

    public function testSetAndGetGender(): void
    {
        $gender = Gender::MALE;
        $this->user->setGender($gender);
        $this->assertEquals($gender, $this->user->getGender());
    }

    public function testSetAndGetCountry(): void
    {
        $country = 'China';
        $this->user->setCountry($country);
        $this->assertEquals($country, $this->user->getCountry());
    }

    public function testSetAndGetProvince(): void
    {
        $province = 'Guangdong';
        $this->user->setProvince($province);
        $this->assertEquals($province, $this->user->getProvince());
    }

    public function testSetAndGetCity(): void
    {
        $city = 'Shenzhen';
        $this->user->setCity($city);
        $this->assertEquals($city, $this->user->getCity());
    }

    public function testSetAndGetLanguage(): void
    {
        $language = Language::zh_CN;
        $this->user->setLanguage($language);
        $this->assertEquals($language, $this->user->getLanguage());
    }

    public function testSetAndGetAccount(): void
    {
        $account = $this->account;
        $this->assertInstanceOf(MiniProgramInterface::class, $account);
        $this->user->setAccount($account);
        $this->assertSame($account, $this->user->getAccount());
    }

    public function testToString(): void
    {
        // 设置ID为空
        $reflectionClass = new \ReflectionClass(User::class);
        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->user, null);

        // 当ID为null时，应该返回空字符串
        $this->assertEquals('', (string) $this->user);

        // 设置ID和OpenId
        $idProperty->setValue($this->user, 1);
        $openId = 'test_open_id';
        $this->user->setOpenId($openId);

        // 设置一个空昵称
        $this->user->setNickName('');

        // 当昵称为空字符串时，应返回openId
        $this->assertEquals($openId, (string) $this->user);

        // 设置昵称
        $nickName = 'test_user';
        $this->user->setNickName($nickName);

        // 当有昵称时，应返回"昵称(openId)"格式
        $this->assertEquals("{$nickName}({$openId})", (string) $this->user);
    }

    public function testGetIdentityValue(): void
    {
        $openId = 'test_open_id';
        $this->user->setOpenId($openId);
        $this->assertEquals($openId, $this->user->getIdentityValue());
    }

    public function testGetIdentityType(): void
    {
        $appId = 'wx12345678';
        $this->account->method('getAppId')->willReturn($appId);
        $account = $this->account;
        $this->assertInstanceOf(MiniProgramInterface::class, $account);
        $this->user->setAccount($account);

        $expectedType = User::IDENTITY_PREFIX . $appId;
        $this->assertEquals($expectedType, $this->user->getIdentityType());
    }

    public function testAddAndGetPhoneNumbers(): void
    {
        // 使用具体类 PhoneNumber 的 mock，理由如下：
        // 1. PhoneNumber 是本包的实体类，与 User 存在 Doctrine 关联关系
        // 2. 测试需要验证实体之间的关联关系是否正确配置
        // 3. 在实际使用中，User 就是与具体的 PhoneNumber 实体类交互的
        $phoneNumber = $this->createMock(PhoneNumber::class);
        $this->user->addPhoneNumber($phoneNumber);

        $phoneNumbers = $this->user->getPhoneNumbers();
        $this->assertCount(1, $phoneNumbers);
        $this->assertSame($phoneNumber, $phoneNumbers->first());
    }

    public function testRemovePhoneNumber(): void
    {
        // 使用具体类 PhoneNumber 的 mock，理由如下：
        // 1. PhoneNumber 是本包的实体类，与 User 存在 Doctrine 关联关系
        // 2. 测试需要验证实体之间的关联关系是否正确配置
        // 3. 在实际使用中，User 就是与具体的 PhoneNumber 实体类交互的
        $phoneNumber = $this->createMock(PhoneNumber::class);
        $this->user->addPhoneNumber($phoneNumber);
        $this->user->removePhoneNumber($phoneNumber);

        $phoneNumbers = $this->user->getPhoneNumbers();
        $this->assertCount(0, $phoneNumbers);
    }
}

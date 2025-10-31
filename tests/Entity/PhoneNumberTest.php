<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramAuthBundle\Entity\User;

/**
 * @internal
 */
#[CoversClass(PhoneNumber::class)]
final class PhoneNumberTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new PhoneNumber();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'phoneNumber' => ['phoneNumber', 'test_value'],
            'countryCode' => ['countryCode', 'test_value'],
            'purePhoneNumber' => ['purePhoneNumber', 'test_value'],
        ];
    }

    private PhoneNumber $phoneNumber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phoneNumber = new PhoneNumber();
    }

    public function testSetAndGetPhoneNumber(): void
    {
        $phone = '+8613800138000';
        $this->phoneNumber->setPhoneNumber($phone);
        $this->assertEquals($phone, $this->phoneNumber->getPhoneNumber());
    }

    public function testSetAndGetPurePhoneNumber(): void
    {
        $purePhone = '13800138000';
        $this->phoneNumber->setPurePhoneNumber($purePhone);
        $this->assertEquals($purePhone, $this->phoneNumber->getPurePhoneNumber());
    }

    public function testSetAndGetCountryCode(): void
    {
        $countryCode = '86';
        $this->phoneNumber->setCountryCode($countryCode);
        $this->assertEquals($countryCode, $this->phoneNumber->getCountryCode());
    }

    public function testSetAndGetRawData(): void
    {
        $rawData = '{"phoneNumber":"+8613800138000","purePhoneNumber":"13800138000","countryCode":"86"}';
        $this->phoneNumber->setRawData($rawData);
        $this->assertEquals($rawData, $this->phoneNumber->getRawData());
    }

    public function testAddAndGetUsers(): void
    {
        // 使用具体类 User 的 mock，理由如下：
        // 1. User 是本包的实体类，与 PhoneNumber 存在 Doctrine 关联关系
        // 2. 测试需要验证实体之间的关联关系是否正确配置
        // 3. 在实际使用中，PhoneNumber 就是与具体的 User 实体类交互的
        $user = $this->createMock(User::class);
        $this->phoneNumber->addUser($user);

        $users = $this->phoneNumber->getUsers();
        $this->assertCount(1, $users);
        $this->assertSame($user, $users->first());
    }

    public function testRemoveUser(): void
    {
        // 使用具体类 User 的 mock，理由如下：
        // 1. User 是本包的实体类，与 PhoneNumber 存在 Doctrine 关联关系
        // 2. 测试需要验证实体之间的关联关系是否正确配置
        // 3. 在实际使用中，PhoneNumber 就是与具体的 User 实体类交互的
        $user = $this->createMock(User::class);
        $this->phoneNumber->addUser($user);
        $this->phoneNumber->removeUser($user);

        $users = $this->phoneNumber->getUsers();
        $this->assertCount(0, $users);
    }

    public function testSetAndGetWatermark(): void
    {
        $watermark = [
            'timestamp' => 1623123456,
            'appid' => 'wx12345678',
        ];
        $this->phoneNumber->setWatermark($watermark);
        $this->assertEquals($watermark, $this->phoneNumber->getWatermark());
    }

    public function testTimestampFields(): void
    {
        $now = new \DateTimeImmutable();
        $this->phoneNumber->setCreateTime($now);
        $this->phoneNumber->setUpdateTime($now);

        $this->assertEquals($now, $this->phoneNumber->getCreateTime());
        $this->assertEquals($now, $this->phoneNumber->getUpdateTime());
    }
}

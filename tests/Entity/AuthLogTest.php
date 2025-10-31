<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use WechatMiniProgramAuthBundle\Entity\AuthLog;

/**
 * @internal
 */
#[CoversClass(AuthLog::class)]
final class AuthLogTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new AuthLog();
    }

    /** @return iterable<array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        yield ['openId', 'test_value'];
        yield ['rawData', 'test_data'];
    }

    private AuthLog $authLog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authLog = new AuthLog();
    }

    public function testSetAndGetOpenId(): void
    {
        $openId = 'test_open_id';
        $this->authLog->setOpenId($openId);
        $this->assertEquals($openId, $this->authLog->getOpenId());
    }

    public function testSetAndGetRawData(): void
    {
        $rawData = '{"openId":"test_open_id"}';
        $this->authLog->setRawData($rawData);
        $this->assertEquals($rawData, $this->authLog->getRawData());
    }

    public function testSetAndGetCreatedFromIp(): void
    {
        $ip = '127.0.0.1';
        $this->authLog->setCreatedFromIp($ip);
        $this->assertEquals($ip, $this->authLog->getCreatedFromIp());
    }

    public function testSetAndGetCreatedBy(): void
    {
        $createdBy = 'admin';
        $this->authLog->setCreatedBy($createdBy);
        $this->assertEquals($createdBy, $this->authLog->getCreatedBy());
    }

    public function testCreateTime(): void
    {
        $now = new \DateTimeImmutable();
        $this->authLog->setCreateTime($now);
        $this->assertEquals($now, $this->authLog->getCreateTime());
    }

    public function testWithEmptyOpenId(): void
    {
        $this->authLog->setOpenId('');
        $this->assertEquals('', $this->authLog->getOpenId());
    }

    public function testWithEmptyRawData(): void
    {
        $this->authLog->setRawData('');
        $this->assertEquals('', $this->authLog->getRawData());
    }

    public function testWithJsonRawData(): void
    {
        $data = ['openId' => 'test_open_id', 'timestamp' => time()];
        $jsonData = json_encode($data);
        self::assertIsString($jsonData, 'JSON encoding should succeed');
        $this->authLog->setRawData($jsonData);
        $this->assertEquals($jsonData, $this->authLog->getRawData());

        // 验证JSON数据可以正确解析
        $rawData = $this->authLog->getRawData();
        self::assertIsString($rawData, 'Raw data should be a string');
        $decodedData = json_decode($rawData, true);
        $this->assertEquals($data, $decodedData);
    }
}

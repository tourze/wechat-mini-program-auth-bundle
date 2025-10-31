<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramBundle\Entity\Account;

/**
 * @internal
 */
#[CoversClass(CodeSessionLog::class)]
final class CodeSessionLogTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new CodeSessionLog();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'code' => ['code', 'test_value'],
            'openId' => ['openId', 'test_value'],
            'sessionKey' => ['sessionKey', 'test_value'],
        ];
    }

    private CodeSessionLog $codeSessionLog;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->codeSessionLog = new CodeSessionLog();
        // 使用具体类 Account 的 mock，理由如下：
        // 1. Account 是外部包的实体类，没有提供接口，我们无法控制其设计
        // 2. 测试需要验证 CodeSessionLog 与 Account 实体的关联关系是否正确
        // 3. 在实际使用中，CodeSessionLog 就是与具体的 Account 实体类交互的
        $this->account = $this->createMock(Account::class);
    }

    public function testSetAndGetCode(): void
    {
        $code = 'test_code';
        $this->codeSessionLog->setCode($code);
        $this->assertEquals($code, $this->codeSessionLog->getCode());
    }

    public function testSetAndGetOpenId(): void
    {
        $openId = 'test_open_id';
        $this->codeSessionLog->setOpenId($openId);
        $this->assertEquals($openId, $this->codeSessionLog->getOpenId());
    }

    public function testSetAndGetUnionId(): void
    {
        $unionId = 'test_union_id';
        $this->codeSessionLog->setUnionId($unionId);
        $this->assertEquals($unionId, $this->codeSessionLog->getUnionId());
    }

    public function testSetAndGetSessionKey(): void
    {
        $sessionKey = 'test_session_key';
        $this->codeSessionLog->setSessionKey($sessionKey);
        $this->assertEquals($sessionKey, $this->codeSessionLog->getSessionKey());
    }

    public function testSetAndGetRawData(): void
    {
        $rawData = '{"key":"value"}';
        $this->codeSessionLog->setRawData($rawData);
        $this->assertEquals($rawData, $this->codeSessionLog->getRawData());
    }

    public function testSetAndGetAccount(): void
    {
        $this->codeSessionLog->setAccount($this->account);
        $this->assertSame($this->account, $this->codeSessionLog->getAccount());
    }

    public function testSetAndGetCreatedFromIp(): void
    {
        $ip = '127.0.0.1';
        $this->codeSessionLog->setCreatedFromIp($ip);
        $this->assertEquals($ip, $this->codeSessionLog->getCreatedFromIp());
    }

    public function testSetAndGetLaunchOptions(): void
    {
        $launchOptions = ['scene' => 1001, 'query' => ['id' => 123]];
        $this->codeSessionLog->setLaunchOptions($launchOptions);
        $this->assertEquals($launchOptions, $this->codeSessionLog->getLaunchOptions());
    }

    public function testSetAndGetEnterOptions(): void
    {
        $enterOptions = ['scene' => 1001, 'query' => ['id' => 123]];
        $this->codeSessionLog->setEnterOptions($enterOptions);
        $this->assertEquals($enterOptions, $this->codeSessionLog->getEnterOptions());
    }

    public function testCreateTime(): void
    {
        $now = new \DateTimeImmutable();
        $this->codeSessionLog->setCreateTime($now);
        $this->assertEquals($now, $this->codeSessionLog->getCreateTime());
    }

    public function testRetrieveLockResource(): void
    {
        $openId = 'test_open_id';
        $this->codeSessionLog->setOpenId($openId);
        $expected = "wechat_mini_program_code_session_log_{$openId}";
        $this->assertEquals($expected, $this->codeSessionLog->retrieveLockResource());
    }

    public function testLaunchOptionsWithNullValue(): void
    {
        $this->codeSessionLog->setLaunchOptions(null);
        $this->assertNull($this->codeSessionLog->getLaunchOptions());
    }

    public function testEnterOptionsWithNullValue(): void
    {
        $this->codeSessionLog->setEnterOptions(null);
        $this->assertNull($this->codeSessionLog->getEnterOptions());
    }
}

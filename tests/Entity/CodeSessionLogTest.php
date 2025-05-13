<?php

namespace WechatMiniProgramAuthBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramBundle\Entity\Account;

class CodeSessionLogTest extends TestCase
{
    private CodeSessionLog $codeSessionLog;
    private Account $account;

    protected function setUp(): void
    {
        $this->codeSessionLog = new CodeSessionLog();
        $this->account = $this->createMock(Account::class);
    }

    public function testSetAndGetCode()
    {
        $code = 'test_code';
        $this->codeSessionLog->setCode($code);
        $this->assertEquals($code, $this->codeSessionLog->getCode());
    }

    public function testSetAndGetOpenId()
    {
        $openId = 'test_open_id';
        $this->codeSessionLog->setOpenId($openId);
        $this->assertEquals($openId, $this->codeSessionLog->getOpenId());
    }

    public function testSetAndGetUnionId()
    {
        $unionId = 'test_union_id';
        $this->codeSessionLog->setUnionId($unionId);
        $this->assertEquals($unionId, $this->codeSessionLog->getUnionId());
    }

    public function testSetAndGetSessionKey()
    {
        $sessionKey = 'test_session_key';
        $this->codeSessionLog->setSessionKey($sessionKey);
        $this->assertEquals($sessionKey, $this->codeSessionLog->getSessionKey());
    }

    public function testSetAndGetRawData()
    {
        $rawData = '{"key":"value"}';
        $this->codeSessionLog->setRawData($rawData);
        $this->assertEquals($rawData, $this->codeSessionLog->getRawData());
    }

    public function testSetAndGetAccount()
    {
        $this->codeSessionLog->setAccount($this->account);
        $this->assertSame($this->account, $this->codeSessionLog->getAccount());
    }

    public function testSetAndGetCreatedFromIp()
    {
        $ip = '127.0.0.1';
        $this->codeSessionLog->setCreatedFromIp($ip);
        $this->assertEquals($ip, $this->codeSessionLog->getCreatedFromIp());
    }

    public function testSetAndGetLaunchOptions()
    {
        $launchOptions = ['scene' => 1001, 'query' => ['id' => 123]];
        $this->codeSessionLog->setLaunchOptions($launchOptions);
        $this->assertEquals($launchOptions, $this->codeSessionLog->getLaunchOptions());
    }

    public function testSetAndGetEnterOptions()
    {
        $enterOptions = ['scene' => 1001, 'query' => ['id' => 123]];
        $this->codeSessionLog->setEnterOptions($enterOptions);
        $this->assertEquals($enterOptions, $this->codeSessionLog->getEnterOptions());
    }

    public function testCreateTime()
    {
        $now = new \DateTimeImmutable();
        $this->codeSessionLog->setCreateTime($now);
        $this->assertEquals($now, $this->codeSessionLog->getCreateTime());
    }
    
    public function testRetrieveLockResource()
    {
        $openId = 'test_open_id';
        $this->codeSessionLog->setOpenId($openId);
        $expected = "wechat_mini_program_code_session_log_{$openId}";
        $this->assertEquals($expected, $this->codeSessionLog->retrieveLockResource());
    }
    
    public function testLaunchOptionsWithNullValue()
    {
        $this->codeSessionLog->setLaunchOptions(null);
        $this->assertNull($this->codeSessionLog->getLaunchOptions());
    }
    
    public function testEnterOptionsWithNullValue()
    {
        $this->codeSessionLog->setEnterOptions(null);
        $this->assertNull($this->codeSessionLog->getEnterOptions());
    }
}
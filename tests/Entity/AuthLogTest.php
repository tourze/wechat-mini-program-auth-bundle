<?php

namespace WechatMiniProgramAuthBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramAuthBundle\Entity\AuthLog;

class AuthLogTest extends TestCase
{
    private AuthLog $authLog;

    protected function setUp(): void
    {
        $this->authLog = new AuthLog();
    }

    public function testSetAndGetOpenId()
    {
        $openId = 'test_open_id';
        $this->authLog->setOpenId($openId);
        $this->assertEquals($openId, $this->authLog->getOpenId());
    }

    public function testSetAndGetRawData()
    {
        $rawData = '{"openId":"test_open_id"}';
        $this->authLog->setRawData($rawData);
        $this->assertEquals($rawData, $this->authLog->getRawData());
    }

    public function testSetAndGetCreatedFromIp()
    {
        $ip = '127.0.0.1';
        $this->authLog->setCreatedFromIp($ip);
        $this->assertEquals($ip, $this->authLog->getCreatedFromIp());
    }

    public function testSetAndGetCreatedBy()
    {
        $createdBy = 'admin';
        $this->authLog->setCreatedBy($createdBy);
        $this->assertEquals($createdBy, $this->authLog->getCreatedBy());
    }

    public function testCreateTime()
    {
        $now = new \DateTimeImmutable();
        $this->authLog->setCreateTime($now);
        $this->assertEquals($now, $this->authLog->getCreateTime());
    }
    
    public function testWithEmptyOpenId()
    {
        $this->authLog->setOpenId('');
        $this->assertEquals('', $this->authLog->getOpenId());
    }
    
    public function testWithEmptyRawData()
    {
        $this->authLog->setRawData('');
        $this->assertEquals('', $this->authLog->getRawData());
    }
    
    public function testWithJsonRawData()
    {
        $data = ['openId' => 'test_open_id', 'timestamp' => time()];
        $jsonData = json_encode($data);
        $this->authLog->setRawData($jsonData);
        $this->assertEquals($jsonData, $this->authLog->getRawData());
        
        // 验证JSON数据可以正确解析
        $decodedData = json_decode($this->authLog->getRawData(), true);
        $this->assertEquals($data, $decodedData);
    }
}
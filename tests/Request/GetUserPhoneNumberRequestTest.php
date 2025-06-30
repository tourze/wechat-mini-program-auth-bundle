<?php

namespace WechatMiniProgramAuthBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramAuthBundle\Request\GetUserPhoneNumberRequest;

class GetUserPhoneNumberRequestTest extends TestCase
{
    private GetUserPhoneNumberRequest $request;

    protected function setUp(): void
    {
        $this->request = new GetUserPhoneNumberRequest();
    }

    public function testGettersAndSetters(): void
    {
        $this->request->setCode('test_code');
        self::assertSame('test_code', $this->request->getCode());
    }

    public function testGetRequestPath(): void
    {
        self::assertSame('/wxa/business/getuserphonenumber', $this->request->getRequestPath());
    }

    public function testGetRequestOptions(): void
    {
        $this->request->setCode('test_code');
        
        $options = $this->request->getRequestOptions();
        
        self::assertArrayHasKey('json', $options);
        self::assertSame('test_code', $options['json']['code']);
    }
}
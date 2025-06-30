<?php

namespace WechatMiniProgramAuthBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramAuthBundle\Request\CodeToSessionRequest;

class CodeToSessionRequestTest extends TestCase
{
    private CodeToSessionRequest $request;

    protected function setUp(): void
    {
        $this->request = new CodeToSessionRequest();
    }

    public function testGettersAndSetters(): void
    {
        $this->request->setJsCode('test_js_code');
        self::assertSame('test_js_code', $this->request->getJsCode());
        
        $this->request->setAppId('test_app_id');
        self::assertSame('test_app_id', $this->request->getAppId());
        
        $this->request->setSecret('test_secret');
        self::assertSame('test_secret', $this->request->getSecret());
        
        $this->request->setGrantType('test_grant_type');
        self::assertSame('test_grant_type', $this->request->getGrantType());
    }

    public function testDefaultGrantType(): void
    {
        self::assertSame('authorization_code', $this->request->getGrantType());
    }

    public function testGetRequestPath(): void
    {
        self::assertSame('/sns/jscode2session', $this->request->getRequestPath());
    }

    public function testGetRequestMethod(): void
    {
        self::assertSame('GET', $this->request->getRequestMethod());
    }

    public function testGetRequestOptions(): void
    {
        $this->request->setAppId('test_app_id');
        $this->request->setSecret('test_secret');
        $this->request->setJsCode('test_js_code');
        
        $options = $this->request->getRequestOptions();
        
        self::assertArrayHasKey('query', $options);
        self::assertSame('test_app_id', $options['query']['appid']);
        self::assertSame('test_secret', $options['query']['secret']);
        self::assertSame('test_js_code', $options['query']['js_code']);
        self::assertSame('authorization_code', $options['query']['grant_type']);
    }

    public function testGetCacheKey(): void
    {
        $this->request->setAppId('test_app_id');
        $this->request->setJsCode('test_js_code');
        
        self::assertSame('CodeToSessionRequest-test_app_id-test_js_code', $this->request->getCacheKey());
    }

    public function testGetCacheDuration(): void
    {
        self::assertSame(3600, $this->request->getCacheDuration());
    }
}
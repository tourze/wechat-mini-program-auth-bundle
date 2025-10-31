<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Request;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatMiniProgramAuthBundle\Request\GetUserPhoneNumberRequest;

/**
 * @internal
 */
#[CoversClass(GetUserPhoneNumberRequest::class)]
final class GetUserPhoneNumberRequestTest extends RequestTestCase
{
    private GetUserPhoneNumberRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

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
        self::assertIsArray($options, 'Request options should be an array');

        self::assertArrayHasKey('json', $options);
        self::assertIsArray($options['json']);
        self::assertSame('test_code', $options['json']['code']);
    }
}

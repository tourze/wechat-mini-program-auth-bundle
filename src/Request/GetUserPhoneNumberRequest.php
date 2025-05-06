<?php

namespace WechatMiniProgramAuthBundle\Request;

use WechatMiniProgramBundle\Request\WithAccountRequest;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-info/phone-number/getPhoneNumber.html
 */
class GetUserPhoneNumberRequest extends WithAccountRequest
{
    /**
     * @var string 手机号获取凭证
     */
    private string $code;

    public function getRequestPath(): string
    {
        return '/wxa/business/getuserphonenumber';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'json' => [
                'code' => $this->getCode(),
            ],
        ];
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }
}

<?php

namespace WechatMiniProgramAuthBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use HttpClientBundle\Request\CacheRequest;

/**
 * @see https://developers.weixin.qq.com/minigame/dev/api-backend/open-api/login/auth.code2Session.html
 * @see https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/others/WeChat_login.html
 */
class CodeToSessionRequest extends ApiRequest implements CacheRequest
{
    /**
     * @var string wx.login 获取的 code
     */
    private string $jsCode;

    private string $grantType = 'authorization_code';

    private string $appId;

    private string $secret;

    public function getRequestPath(): string
    {
        return '/sns/jscode2session';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'query' => [
                'appid' => $this->getAppId(),
                'secret' => $this->getSecret(),
                'js_code' => $this->getJsCode(),
                'grant_type' => $this->getGrantType(),
            ],
        ];
    }

    public function getRequestMethod(): ?string
    {
        return 'GET';
    }

    public function getJsCode(): string
    {
        return $this->jsCode;
    }

    public function setJsCode(string $jsCode): void
    {
        $this->jsCode = $jsCode;
    }

    public function getGrantType(): string
    {
        return $this->grantType;
    }

    public function setGrantType(string $grantType): void
    {
        $this->grantType = $grantType;
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function setAppId(string $appId): void
    {
        $this->appId = $appId;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    public function getCacheKey(): string
    {
        return "CodeToSessionRequest-{$this->getAppId()}-{$this->getJsCode()}";
    }

    public function getCacheDuration(): int
    {
        return 60 * 60;
    }
}

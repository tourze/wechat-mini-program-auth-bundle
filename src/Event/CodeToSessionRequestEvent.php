<?php

namespace WechatMiniProgramAuthBundle\Event;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Event\LaunchOptionsAware;

/**
 * 微信小程序 code2session 请求
 */
class CodeToSessionRequestEvent extends Event
{
    use LaunchOptionsAware;

    private Account $account;

    private string $code;

    private CodeSessionLog $codeSessionLog;

    /**
     * @var array|null 当需要拦截返回值时，我们通过这里来控制
     */
    private ?array $return = null;

    private UserInterface|null $bizUser = null;

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getCodeSessionLog(): CodeSessionLog
    {
        return $this->codeSessionLog;
    }

    public function setCodeSessionLog(CodeSessionLog $codeSessionLog): void
    {
        $this->codeSessionLog = $codeSessionLog;
    }

    public function getReturn(): ?array
    {
        return $this->return;
    }

    public function setReturn(?array $return): void
    {
        $this->return = $return;
    }

    public function getBizUser(): UserInterface|null
    {
        return $this->bizUser;
    }

    public function setBizUser(UserInterface|null $bizUser): void
    {
        $this->bizUser = $bizUser;
    }
}

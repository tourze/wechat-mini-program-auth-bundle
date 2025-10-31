<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Event;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramBundle\Event\LaunchOptionsAware;

/**
 * 微信小程序 code2session 请求
 */
class CodeToSessionRequestEvent extends Event
{
    use LaunchOptionsAware;

    private MiniProgramInterface $account;

    private string $code;

    private CodeSessionLog $codeSessionLog;

    /**
     * @var array<string, mixed>|null 当需要拦截返回值时，我们通过这里来控制
     */
    private ?array $return = null;

    private ?UserInterface $bizUser = null;

    public function getAccount(): MiniProgramInterface
    {
        return $this->account;
    }

    public function setAccount(MiniProgramInterface $account): void
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

    /**
     * @return array<string, mixed>|null
     */
    public function getReturn(): ?array
    {
        return $this->return;
    }

    /**
     * @param array<string, mixed>|null $return
     */
    public function setReturn(?array $return): void
    {
        $this->return = $return;
    }

    public function getBizUser(): ?UserInterface
    {
        return $this->bizUser;
    }

    public function setBizUser(?UserInterface $bizUser): void
    {
        $this->bizUser = $bizUser;
    }
}

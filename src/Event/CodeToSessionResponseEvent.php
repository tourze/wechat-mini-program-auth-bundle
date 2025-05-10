<?php

namespace WechatMiniProgramAuthBundle\Event;

use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\UserEventBundle\Event\UserInteractionContext;
use Tourze\UserEventBundle\Event\UserInteractionEvent;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramBundle\Event\LaunchOptionsAware;

class CodeToSessionResponseEvent extends UserInteractionEvent implements UserInteractionContext
{
    use LaunchOptionsAware;

    private array $result = [];

    public function getResult(): array
    {
        return $this->result;
    }

    public function setResult(array $result): void
    {
        $this->result = $result;
    }

    /**
     * 是否新用户
     */
    public bool $newUser;

    private CodeSessionLog $codeSessionLog;

    /**
     * @var UserInterface TODO 貌似跟 sender 重复了，考虑下要不要删除
     */
    private UserInterface $bizUser;

    private \Tourze\WechatMiniProgramUserContracts\UserInterface $wechatUser;

    public function getCodeSessionLog(): CodeSessionLog
    {
        return $this->codeSessionLog;
    }

    public function setCodeSessionLog(CodeSessionLog $codeSessionLog): void
    {
        $this->codeSessionLog = $codeSessionLog;
    }

    public function getBizUser(): UserInterface
    {
        return $this->bizUser;
    }

    public function setBizUser(UserInterface $bizUser): void
    {
        $this->bizUser = $bizUser;
    }

    public function getWechatUser(): \Tourze\WechatMiniProgramUserContracts\UserInterface
    {
        return $this->wechatUser;
    }

    public function setWechatUser(\Tourze\WechatMiniProgramUserContracts\UserInterface $wechatUser): void
    {
        $this->wechatUser = $wechatUser;
    }

    public function isNewUser(): bool
    {
        return $this->newUser;
    }

    public function setNewUser(bool $newUser): void
    {
        $this->newUser = $newUser;
    }

    public function getContext(): array
    {
        return [
            'wechatUser' => $this->getWechatUser(),
            'codeSessionLog' => $this->getCodeSessionLog(),
        ];
    }
}

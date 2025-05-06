<?php

namespace WechatMiniProgramAuthBundle\Event;

use AppBundle\Entity\BizUser;
use Tourze\UserEventBundle\Event\UserInteractionContext;
use Tourze\UserEventBundle\Event\UserInteractionEvent;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Entity\User;
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
     * @var BizUser TODO 貌似跟 sender 重复了，考虑下要不要删除
     */
    private BizUser $bizUser;

    private User $wechatUser;

    public function getCodeSessionLog(): CodeSessionLog
    {
        return $this->codeSessionLog;
    }

    public function setCodeSessionLog(CodeSessionLog $codeSessionLog): void
    {
        $this->codeSessionLog = $codeSessionLog;
    }

    public function getBizUser(): BizUser
    {
        return $this->bizUser;
    }

    public function setBizUser(BizUser $bizUser): void
    {
        $this->bizUser = $bizUser;
    }

    public function getWechatUser(): User
    {
        return $this->wechatUser;
    }

    public function setWechatUser(User $wechatUser): void
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

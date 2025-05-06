<?php

namespace WechatMiniProgramAuthBundle\Event;

use Tourze\UserEventBundle\Event\UserInteractionEvent;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramBundle\Event\LaunchOptionsAware;

class ChangePhoneNumberEvent extends UserInteractionEvent
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

    private PhoneNumber $phoneNumber;

    private User $wechatUser;

    public function getPhoneNumber(): PhoneNumber
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(PhoneNumber $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getWechatUser(): User
    {
        return $this->wechatUser;
    }

    public function setWechatUser(User $wechatUser): void
    {
        $this->wechatUser = $wechatUser;
    }
}

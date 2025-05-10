<?php

namespace WechatMiniProgramAuthBundle\Event;

use Tourze\JsonRPCEndpointBundle\Traits\AppendJsonRpcResultAware;
use Tourze\UserEventBundle\Event\UserInteractionEvent;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramBundle\Event\LaunchOptionsAware;

class GetPhoneNumberEvent extends UserInteractionEvent
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

    use AppendJsonRpcResultAware;

    private PhoneNumber $phoneNumber;

    private UserInterface $wechatUser;

    private string $source = '';

    public function getPhoneNumber(): PhoneNumber
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(PhoneNumber $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getWechatUser(): UserInterface
    {
        return $this->wechatUser;
    }

    public function setWechatUser(UserInterface $wechatUser): void
    {
        $this->wechatUser = $wechatUser;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }
}

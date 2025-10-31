<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIpBundle\Traits\CreatedFromIpAware;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;
use Tourze\DoctrineUserBundle\Traits\CreatedByAware;
use WechatMiniProgramAuthBundle\Repository\AuthLogRepository;

/**
 * 授权日志
 */
#[ORM\Entity(repositoryClass: AuthLogRepository::class)]
#[ORM\Table(name: 'wechat_mini_program_auth_log', options: ['comment' => '授权日志'])]
class AuthLog implements \Stringable
{
    use CreatedByAware;
    use CreatedFromIpAware;
    use CreateTimeAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键'])]
    private ?int $id = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => 'OpenID'])]
    private ?string $openId = null;

    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '原始数据'])]
    private ?string $rawData = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpenId(): ?string
    {
        return $this->openId;
    }

    public function setOpenId(?string $openId): void
    {
        $this->openId = $openId;
    }

    public function getRawData(): ?string
    {
        return $this->rawData;
    }

    public function setRawData(?string $rawData): void
    {
        $this->rawData = $rawData;
    }

    public function __toString(): string
    {
        return (string) ($this->id ?? 'new');
    }
}

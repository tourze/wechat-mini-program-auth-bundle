<?php

namespace WechatMiniProgramAuthBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineUserBundle\Traits\CreatedByAware;
use WechatMiniProgramAuthBundle\Repository\AuthLogRepository;

/**
 * 授权日志
 */
#[ORM\Entity(repositoryClass: AuthLogRepository::class)]
#[ORM\Table(name: 'wechat_mini_program_auth_log', options: ['comment' => '授权日志'])]
class AuthLog implements Stringable
{
    use CreatedByAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键'])]
    private ?int $id = 0;

    private ?string $openId = null;

    private ?string $rawData = null;

    #[CreateIpColumn]
    private ?string $createdFromIp = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeImmutable $createTime = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpenId(): ?string
    {
        return $this->openId;
    }

    public function setOpenId(string $openId): self
    {
        $this->openId = $openId;

        return $this;
    }

    public function getRawData(): ?string
    {
        return $this->rawData;
    }

    public function setRawData(string $rawData): self
    {
        $this->rawData = $rawData;

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setCreatedFromIp(?string $createdFromIp): self
    {
        $this->createdFromIp = $createdFromIp;

        return $this;
    }

    public function setCreateTime(?\DateTimeImmutable $createdAt): void
    {
        $this->createTime = $createdAt;
    }

    public function getCreateTime(): ?\DateTimeImmutable
    {
        return $this->createTime;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}

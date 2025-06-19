<?php

namespace WechatMiniProgramAuthBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\LockServiceBundle\Model\LockEntity;
use Tourze\ScheduleEntityCleanBundle\Attribute\AsScheduleClean;
use WechatMiniProgramAuthBundle\Repository\CodeSessionLogRepository;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Entity\LaunchOptionsAware;

#[AsScheduleClean(expression: '30 5 * * *', defaultKeepDay: 18, keepDayEnv: 'CODE_SESSION_PERSIST_DAY')]
#[ORM\Entity(repositoryClass: CodeSessionLogRepository::class)]
#[ORM\Table(name: 'wechat_mini_program_code_session_log', options: ['comment' => 'code2session日志'])]
class CodeSessionLog implements LockEntity
, \Stringable{
    use LaunchOptionsAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Account $account = null;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => 'Code'])]
    private string $code;

    #[IndexColumn]
    private ?string $openId = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => 'UnionID'])]
    private ?string $unionId = null;

    #[ORM\Column(type: Types::STRING, length: 120, options: ['comment' => 'SessionKey'])]
    private ?string $sessionKey = null;

    private ?string $rawData = null;

    #[ORM\Column(length: 45, nullable: true, options: ['comment' => '创建时IP'])]
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

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getUnionId(): ?string
    {
        return $this->unionId;
    }

    public function setUnionId(string $unionId): self
    {
        $this->unionId = $unionId;

        return $this;
    }

    public function getSessionKey(): ?string
    {
        return $this->sessionKey;
    }

    public function setSessionKey(string $sessionKey): self
    {
        $this->sessionKey = $sessionKey;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setCreatedFromIp(?string $createdFromIp): void
    {
        $this->createdFromIp = $createdFromIp;
    }

    public function setCreateTime(?\DateTimeImmutable $createdAt): self
    {
        $this->createTime = $createdAt;

        return $this;
    }

    public function getCreateTime(): ?\DateTimeImmutable
    {
        return $this->createTime;
    }

    public function retrieveLockResource(): string
    {
        return "wechat_mini_program_code_session_log_{$this->getOpenId()}";
    }


    public function __toString(): string
    {
        return sprintf('%s #%s', 'CodeSessionLog', $this->id ?? 'new');
    }
}

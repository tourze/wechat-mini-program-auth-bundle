<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\CreatedFromIpAware;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;
use Tourze\LockServiceBundle\Model\LockEntity;
use Tourze\ScheduleEntityCleanBundle\Attribute\AsScheduleClean;
use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use WechatMiniProgramAuthBundle\Repository\CodeSessionLogRepository;
use WechatMiniProgramBundle\Entity\LaunchOptionsAware;

#[AsScheduleClean(expression: '30 5 * * *', defaultKeepDay: 18, keepDayEnv: 'CODE_SESSION_PERSIST_DAY')]
#[ORM\Entity(repositoryClass: CodeSessionLogRepository::class)]
#[ORM\Table(name: 'wechat_mini_program_code_session_log', options: ['comment' => 'code2session日志'])]
class CodeSessionLog implements LockEntity, \Stringable
{
    use LaunchOptionsAware;
    use CreatedFromIpAware;
    use CreateTimeAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = null; // @phpstan-ignore-line property.unusedType Doctrine ORM assigns ID after persist

    #[ORM\ManyToOne(targetEntity: MiniProgramInterface::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?MiniProgramInterface $account = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => 'Code'])]
    private string $code;

    #[Assert\Length(max: 255)]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => 'OpenID'])]
    private ?string $openId = null;

    #[Assert\Length(max: 100)]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => 'UnionID'])]
    private ?string $unionId = null;

    #[Assert\Length(max: 120)]
    #[ORM\Column(type: Types::STRING, length: 120, nullable: true, options: ['comment' => 'SessionKey'])]
    private ?string $sessionKey = null;

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

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getUnionId(): ?string
    {
        return $this->unionId;
    }

    public function setUnionId(?string $unionId): void
    {
        $this->unionId = $unionId;
    }

    public function getSessionKey(): ?string
    {
        return $this->sessionKey;
    }

    public function setSessionKey(?string $sessionKey): void
    {
        $this->sessionKey = $sessionKey;
    }

    public function getAccount(): ?MiniProgramInterface
    {
        return $this->account;
    }

    public function setAccount(?MiniProgramInterface $account): void
    {
        $this->account = $account;
    }

    public function retrieveLockResource(): string
    {
        return "wechat_mini_program_code_session_log_{$this->getOpenId()}";
    }

    public function __toString(): string
    {
        return sprintf('CodeSessionLog #%s', $this->id ?? 'new');
    }
}

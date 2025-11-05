<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use WechatMiniProgramAuthBundle\Repository\PhoneNumberRepository;
use WechatMiniProgramBundle\Entity\LaunchOptionsAware;

/**
 * 授权手机号码
 *
 * 一个微信用户，有可能会授权多个手机号码的喔
 * 同一个手机号码，也可能有多个人一起使用，所以这个不能直接当做唯一标志
 */
#[ORM\Entity(repositoryClass: PhoneNumberRepository::class)]
#[ORM\Table(name: 'wechat_mini_program_phone_number', options: ['comment' => '授权手机号'])]
class PhoneNumber implements \Stringable
{
    use TimestampableAware;
    use LaunchOptionsAware;
    use IpTraceableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'phoneNumbers', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(
        name: 'wechat_mini_program_phone_number_user',
        joinColumns: [new ORM\JoinColumn(name: 'phone_number_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    )]
    private Collection $users;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\+?[1-9]\d{1,14}$/')]
    #[Assert\Length(max: 255)]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '用户绑定的手机号（国外手机号会有区号）'])]
    private ?string $phoneNumber = null;

    #[Assert\Regex(pattern: '/^\d{7,15}$/')]
    #[Assert\Length(max: 40)]
    #[ORM\Column(type: Types::STRING, length: 40, nullable: true, options: ['comment' => '没有区号的手机号'])]
    private ?string $purePhoneNumber = null;

    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '区号'])]
    private ?string $countryCode = null;

    /**
     * @var array<string, mixed>|null
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '数据水印'])]
    private ?array $watermark = [];

    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $rawData = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): void
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    public function removeUser(User $user): void
    {
        $this->users->removeElement($user);
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getPurePhoneNumber(): ?string
    {
        return $this->purePhoneNumber;
    }

    public function setPurePhoneNumber(?string $purePhoneNumber): void
    {
        $this->purePhoneNumber = $purePhoneNumber;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getWatermark(): ?array
    {
        return $this->watermark;
    }

    /**
     * @param array<string, mixed>|null $watermark
     */
    public function setWatermark(?array $watermark): void
    {
        $this->watermark = $watermark;
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

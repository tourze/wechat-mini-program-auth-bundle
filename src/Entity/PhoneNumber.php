<?php

namespace WechatMiniProgramAuthBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use WechatMiniProgramAuthBundle\Repository\PhoneNumberRepository;
use WechatMiniProgramBundle\Entity\LaunchOptionsAware;

/**
 * 授权手机号码
 *
 * 一个微信用户，有可能会授权多个手机号码的喔
 * 同一个手机号码，也可能有多个人一起使用，所以这个不能直接当做唯一标志
 */
#[Deletable]
#[ORM\Entity(repositoryClass: PhoneNumberRepository::class)]
#[ORM\Table(name: 'wechat_mini_program_phone_number', options: ['comment' => '授权手机号'])]
class PhoneNumber
{
    use TimestampableAware;
    use LaunchOptionsAware;

    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    /**
     * @var Collection<User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'phoneNumbers', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private Collection $users;

    /**
     * 用户绑定的手机号（国外手机号会有区号）.
     */
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 40, unique: true, options: ['comment' => '用户绑定的手机号'])]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: Types::STRING, length: 40, nullable: true, options: ['comment' => '没有区号的手机号'])]
    private ?string $purePhoneNumber = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '区号'])]
    private ?string $countryCode = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '数据水印'])]
    private ?array $watermark = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rawData = null;

    #[CreateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '更新时IP'])]
    private ?string $updatedFromIp = null;

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

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getPurePhoneNumber(): ?string
    {
        return $this->purePhoneNumber;
    }

    public function setPurePhoneNumber(?string $purePhoneNumber): self
    {
        $this->purePhoneNumber = $purePhoneNumber;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getWatermark(): ?array
    {
        return $this->watermark;
    }

    public function setWatermark(?array $watermark): self
    {
        $this->watermark = $watermark;

        return $this;
    }

    public function getRawData(): ?string
    {
        return $this->rawData;
    }

    public function setRawData(?string $rawData): self
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

    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
    }

    public function setUpdatedFromIp(?string $updatedFromIp): self
    {
        $this->updatedFromIp = $updatedFromIp;

        return $this;
    }}

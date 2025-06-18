<?php

namespace WechatMiniProgramAuthBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\EasyAdmin\Attribute\Column\PictureColumn;
use Tourze\UserIDBundle\Contracts\IdentityInterface;
use Tourze\UserIDBundle\Model\Identity;
use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use WechatMiniProgramAuthBundle\Enum\Gender;
use WechatMiniProgramAuthBundle\Enum\Language;
use WechatMiniProgramAuthBundle\Repository\UserRepository;
use WechatMiniProgramBundle\Entity\Account;

/**
 * 微信用户信息
 *
 * 包含微信公众号、小程序、开放平台、微信小程序等渠道的用户信息
 * 字段上参考微信的文档： https://developers.weixin.qq.com/miniprogram/dev/api/open-api/user-info/UserInfo.html
 * 之所以单独搞这个模型，是为了方便后续统计、对数。
 * 目前微信体系中，用户信息并不总是能返回完整的了，我们需要根据实际，调用其他接口来补充信息。
 * 参考 https://developers.weixin.qq.com/community/develop/doc/00028edbe3c58081e7cc834705b801
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'wechat_mini_program_user', options: ['comment' => '微信小程序用户'])]
class User implements \Stringable, IdentityInterface, \Tourze\WechatMiniProgramUserContracts\UserInterface
{
    use TimestampableAware;
    public const IDENTITY_PREFIX = 'wechat-mini-program-';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Account $account = null;

    private string $openId;

    #[IndexColumn]
    private ?string $unionId = null;

    private ?string $nickName = null;

    /**
     * 用户头像图片的 URL。
     * URL 最后一个数值代表正方形头像大小（有 0、46、64、96、132 数值可选，0 代表 640x640 的正方形头像，46 表示 46x46 的正方形头像，剩余数值以此类推。默认132）。
     * 用户没有头像时该项为空。
     * 若用户更换头像，原有头像 URL 将失效。
     */
    #[PictureColumn]
    private ?string $avatarUrl = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, enumType: Gender::class, options: ['comment' => '性别'])]
    private ?Gender $gender = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '国家'])]
    private ?string $country = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '省份'])]
    private ?string $province = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '地区'])]
    private ?string $city = null;

    private Language $language;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '原始数据'])]
    private ?string $rawData = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true, options: ['comment' => '已授权scope'])]
    private ?array $authorizeScopes = [];

    /**
     * @var Collection<PhoneNumber>
     */
    #[Ignore]
    #[ORM\ManyToMany(targetEntity: PhoneNumber::class, mappedBy: 'users', fetch: 'EXTRA_LAZY')]
    private Collection $phoneNumbers;

    #[ORM\ManyToOne(targetEntity: UserInterface::class, cascade: ['persist'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?UserInterface $user = null;

    #[CreateIpColumn]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    private ?string $updatedFromIp = null;

    public function __construct()
    {
        $this->phoneNumbers = new ArrayCollection();
    }

    public function __toString(): string
    {
        if ($this->getId() === null) {
            return '';
        }

        if (0 === mb_strlen($this->getNickName())) {
            return $this->getOpenId();
        }

        return "{$this->getNickName()}({$this->getOpenId()})";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
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

    public function getOpenId(): string
    {
        return $this->openId;
    }

    public function setOpenId(string $openId): self
    {
        $this->openId = $openId;

        return $this;
    }

    public function getUnionId(): ?string
    {
        return $this->unionId;
    }

    public function setUnionId(?string $unionId): self
    {
        $this->unionId = $unionId;

        return $this;
    }

    public function getNickName(): ?string
    {
        return $this->nickName;
    }

    public function setNickName(?string $nickName): self
    {
        $this->nickName = $nickName;

        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): self
    {
        $this->avatarUrl = $avatarUrl;

        return $this;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(?Gender $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(?string $province): self
    {
        $this->province = $province;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setLanguage(Language $language): self
    {
        $this->language = $language;

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

    public function getAuthorizeScopes(): ?array
    {
        return $this->authorizeScopes;
    }

    public function setAuthorizeScopes(?array $authorizeScopes): self
    {
        $this->authorizeScopes = $authorizeScopes;

        return $this;
    }

    /**
     * @return Collection<int, PhoneNumber>
     */
    public function getPhoneNumbers(): Collection
    {
        return $this->phoneNumbers;
    }

    public function addPhoneNumber(PhoneNumber $phoneNumber): self
    {
        if (!$this->phoneNumbers->contains($phoneNumber)) {
            $this->phoneNumbers[] = $phoneNumber;
            $phoneNumber->addUser($this);
        }

        return $this;
    }

    public function removePhoneNumber(PhoneNumber $phoneNumber): self
    {
        if ($this->phoneNumbers->removeElement($phoneNumber)) {
            $phoneNumber->removeUser($this);
        }

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getIdentityValue(): string
    {
        return $this->getOpenId();
    }

    public function getIdentityType(): string
    {
        return self::IDENTITY_PREFIX . $this->getAccount()->getAppId();
    }

    public function getIdentityArray(): \Traversable
    {
        yield new Identity((string) $this->getId(), $this->getIdentityType(), $this->getIdentityValue(), [
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
        ]);
        $unionId = $this->getUnionId();
        if ($unionId !== null) {
            yield new Identity((string) $this->getId(), 'wechat-unionid', $unionId, [
                'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
                'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            ]);
        }
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
    }public function getAccounts(): array
    {
        return [];
    }

    public function getMiniProgram(): MiniProgramInterface
    {
        return $this->getAccount();
    }
}

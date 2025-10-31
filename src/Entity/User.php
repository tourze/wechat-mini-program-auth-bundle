<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\LockServiceBundle\Model\LockEntity;
use Tourze\UserIDBundle\Contracts\IdentityInterface;
use Tourze\UserIDBundle\Model\Identity;
use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use WechatMiniProgramAuthBundle\Enum\Gender;
use WechatMiniProgramAuthBundle\Enum\Language;
use WechatMiniProgramAuthBundle\Exception\AccountNotFoundException;
use WechatMiniProgramAuthBundle\Repository\UserRepository;

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
class User implements \Stringable, IdentityInterface, \Tourze\WechatMiniProgramUserContracts\UserInterface, LockEntity
{
    use TimestampableAware;
    use IpTraceableAware;
    public const IDENTITY_PREFIX = 'wechat-mini-program-';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MiniProgramInterface::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?MiniProgramInterface $account = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 120, unique: true, options: ['comment' => 'OpenID'])]
    private string $openId;

    #[Assert\Length(max: 120)]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 120, nullable: true, options: ['comment' => 'UnionID'])]
    private ?string $unionId = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '昵称'])]
    private ?string $nickName = null;

    #[Assert\Url]
    #[Assert\Length(max: 500)]
    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '头像URL'])]
    private ?string $avatarUrl = null;

    #[Assert\Choice(callback: [Gender::class, 'cases'])]
    #[ORM\Column(type: Types::INTEGER, nullable: true, enumType: Gender::class, options: ['comment' => '性别'])]
    private ?Gender $gender = null;

    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '国家'])]
    private ?string $country = null;

    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '省份'])]
    private ?string $province = null;

    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '地区'])]
    private ?string $city = null;

    #[Assert\NotNull]
    #[Assert\Choice(callback: [Language::class, 'cases'])]
    #[ORM\Column(type: Types::STRING, length: 10, nullable: false, enumType: Language::class, options: ['comment' => '语言', 'default' => 'zh_CN'])]
    private Language $language = Language::zh_CN;

    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '原始数据'])]
    private ?string $rawData = null;

    /**
     * @var string[]|null
     */
    #[Assert\All(constraints: [new Assert\Type(type: 'string')])]
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true, options: ['comment' => '已授权scope'])]
    private ?array $authorizeScopes = [];

    /**
     * @var Collection<int, PhoneNumber>
     */
    #[Ignore]
    #[ORM\ManyToMany(targetEntity: PhoneNumber::class, mappedBy: 'users', fetch: 'EXTRA_LAZY')]
    private Collection $phoneNumbers;

    #[ORM\ManyToOne(targetEntity: UserInterface::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?UserInterface $user = null;

    public function __construct()
    {
        $this->phoneNumbers = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        if (0 === mb_strlen($this->getNickName() ?? '')) {
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

    public function getAccount(): ?MiniProgramInterface
    {
        return $this->account;
    }

    public function setAccount(?MiniProgramInterface $account): void
    {
        $this->account = $account;
    }

    public function getOpenId(): string
    {
        return $this->openId;
    }

    public function setOpenId(string $openId): void
    {
        $this->openId = $openId;
    }

    public function getUnionId(): ?string
    {
        return $this->unionId;
    }

    public function setUnionId(?string $unionId): void
    {
        $this->unionId = $unionId;
    }

    public function getNickName(): ?string
    {
        return $this->nickName;
    }

    public function setNickName(?string $nickName): void
    {
        $this->nickName = $nickName;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): void
    {
        $this->avatarUrl = $avatarUrl;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(?Gender $gender): void
    {
        $this->gender = $gender;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(?string $province): void
    {
        $this->province = $province;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setLanguage(Language $language): void
    {
        $this->language = $language;
    }

    public function getRawData(): ?string
    {
        return $this->rawData;
    }

    public function setRawData(?string $rawData): void
    {
        $this->rawData = $rawData;
    }

    /**
     * @return string[]|null
     */
    public function getAuthorizeScopes(): ?array
    {
        return $this->authorizeScopes;
    }

    /**
     * @param string[]|null $authorizeScopes
     */
    public function setAuthorizeScopes(?array $authorizeScopes): void
    {
        $this->authorizeScopes = $authorizeScopes;
    }

    /**
     * @return Collection<int, PhoneNumber>
     */
    public function getPhoneNumbers(): Collection
    {
        return $this->phoneNumbers;
    }

    public function addPhoneNumber(PhoneNumber $phoneNumber): void
    {
        if (!$this->phoneNumbers->contains($phoneNumber)) {
            $this->phoneNumbers->add($phoneNumber);
            $phoneNumber->addUser($this);
        }
    }

    public function removePhoneNumber(PhoneNumber $phoneNumber): void
    {
        if ($this->phoneNumbers->removeElement($phoneNumber)) {
            $phoneNumber->removeUser($this);
        }
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
    }

    public function getIdentityValue(): string
    {
        return $this->getOpenId();
    }

    public function getIdentityType(): string
    {
        $account = $this->getAccount();
        if (null === $account) {
            throw new AccountNotFoundException('Account cannot be null when getting identity type');
        }

        return self::IDENTITY_PREFIX . $account->getAppId();
    }

    /**
     * @return \Generator<Identity>
     */
    public function getIdentityArray(): \Traversable
    {
        yield new Identity((string) $this->getId(), $this->getIdentityType(), $this->getIdentityValue(), [
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
        ]);
        $unionId = $this->getUnionId();
        if (null !== $unionId) {
            yield new Identity((string) $this->getId(), 'wechat-unionid', $unionId, [
                'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
                'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            ]);
        }
    }

    public function getAccounts(): array
    {
        return [];
    }

    public function getMiniProgram(): MiniProgramInterface
    {
        $account = $this->getAccount();
        if (null === $account) {
            throw new AccountNotFoundException('Account cannot be null when getting mini program');
        }

        return $account;
    }

    public function retrieveLockResource(): string
    {
        return 'wechat_mini_program_user_' . $this->getOpenId();
    }
}

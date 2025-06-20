<?php

namespace WechatMiniProgramAuthBundle\Repository;

use BizUserBundle\Entity\BizUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\LockServiceBundle\Service\LockService;
use Tourze\UserIDBundle\Model\SystemUser;
use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use Tourze\WechatMiniProgramUserContracts\UserInterface as WechatMiniProgramUserInterface;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface as WechatMiniProgramUserLoaderInterface;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramBundle\WechatMiniProgramBundle;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
#[AsAlias(id: WechatMiniProgramUserLoaderInterface::class)]
class UserRepository extends ServiceEntityRepository implements WechatMiniProgramUserLoaderInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly UserLoaderInterface $userLoader,
        private readonly LockService $lockService,
    ) {
        parent::__construct($registry, User::class);
    }

    /**
     * 查找指定系统用户相关的微信小程序用户
     */
    public function getBySysUser(UserInterface $sysUser): ?User
    {
        $user = $this->findOneBy(['user' => $sysUser]);
        if ($user === null) {
            $user = $this->findOneBy(['openId' => $sysUser->getUserIdentifier()]);
        }
        if ($user === null) {
            $user = $this->findOneBy(['unionId' => $sysUser->getUserIdentifier()]);
        }

        return $user;
    }

    /**
     * 查找对应的微信小程序用户
     */
    public function transformToWechatUser(BizUser|UserInterface $user): ?User
    {
        if ($user instanceof SystemUser) {
            return null;
        }

        $wechatUser = $this->findOneBy([
            'openId' => $user->getUsername(),
        ]);
        if ($wechatUser !== null) {
            return $wechatUser;
        }

        if ($user->getIdentity()) {
            $wechatUser = $this->findOneBy([
                'unionId' => $user->getIdentity(),
            ]);
            if ($wechatUser !== null) {
                return $wechatUser;
            }
        }

        return null;
    }

    /**
     * 微信用户归微信用户，在实际开发中，我们实际存储的一般是最上层的User，所以此处会有一个转换和同步
     */
    public function transformToSysUser(User $entity): UserInterface
    {
        $bizUser = $entity->getUser();
        if ($bizUser !== null) {
            return $bizUser;
        }
        $bizUser = $this->userLoader->loadUserByIdentifier($entity->getOpenId());
        if ($bizUser === null) {
            $bizUser = new BizUser();
            $bizUser->setUsername($entity->getOpenId());
            $bizUser->setNickName($entity->getNickName() ?: $_ENV['DEFAULT_NICK_NAME']);
            $bizUser->setValid(true);
        }

        if ($entity->getUnionId()) {
            $bizUser->setIdentity($entity->getUnionId());
        }

        if (!$bizUser->getNickName()) {
            $bizUser->setNickName($entity->getNickName() ?: $_ENV['DEFAULT_NICK_NAME']);
        }
        if (!empty($entity->getNickName())) {
            $bizUser->setNickName($entity->getNickName());
        }

        if ($entity->getAvatarUrl()) {
            $bizUser->setAvatar($entity->getAvatarUrl());
        }
        if (!$bizUser->getAvatar()) {
            $bizUser->setAvatar(WechatMiniProgramBundle::DEFAULT_AVATAR);
        }

        $this->getEntityManager()->persist($bizUser);
        $this->getEntityManager()->flush();

        return $bizUser;
    }

    public function loadUserByOpenId(string $openId): ?WechatMiniProgramUserInterface
    {
        return $this->findOneBy(['openId' => $openId]);
    }

    public function loadUserByUnionId(string $unionId): ?WechatMiniProgramUserInterface
    {
        return $this->findOneBy(['unionId' => $unionId]);
    }

    public function createUser(MiniProgramInterface $miniProgram, string $openId, ?string $unionId = null): WechatMiniProgramUserInterface
    {
        $lock = $this->lockService->acquireLock("wechat-mini-program-auth-bundle_user_$openId");

        try {
            $user = $this->loadUserByOpenId($openId);
            if ($user === null) {
                $user = new User();
                $user->setAccount($miniProgram);
                $user->setOpenId($openId);
                $user->setUnionId($unionId);
                $this->getEntityManager()->persist($user);
                $this->getEntityManager()->flush();
            }
            return $user;
        } finally {
            $lock->release();
        }
    }
}

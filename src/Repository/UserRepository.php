<?php

namespace WechatMiniProgramAuthBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\LockServiceBundle\Service\LockService;
use Tourze\UserIDBundle\Model\SystemUser;
use Tourze\UserServiceContracts\UserManagerInterface;
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
    public function transformToWechatUser(UserInterface $user): ?User
    {
        if ($user instanceof SystemUser) {
            return null;
        }

        $wechatUser = $this->findOneBy([
            'openId' => $user->getUserIdentifier(),
        ]);
        if ($wechatUser !== null) {
            return $wechatUser;
        }

        if (method_exists($user, 'getIdentity') && $user->getIdentity()) {
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
            // 如果用户不存在，使用 UserManagerInterface 创建
            /** @var UserManagerInterface $userManager */
            $userManager = $this->userLoader;
            if ($userManager instanceof UserManagerInterface) {
                $nickName = $entity->getNickName() ?: $_ENV['DEFAULT_NICK_NAME'];
                $avatarUrl = $entity->getAvatarUrl() ?: WechatMiniProgramBundle::DEFAULT_AVATAR;
                $bizUser = $userManager->createUser($entity->getOpenId(), $nickName, $avatarUrl);
                
                if (!empty($entity->getUnionId()) && method_exists($bizUser, 'setIdentity')) {
                    $bizUser->setIdentity($entity->getUnionId());
                    $this->getEntityManager()->persist($bizUser);
                    $this->getEntityManager()->flush();
                }
            } else {
                // 如果 userLoader 不是 UserManagerInterface，抛出异常
                throw new \RuntimeException('UserLoader must implement UserManagerInterface to create new users');
            }
        } else {
            // 更新现有用户信息
            $needUpdate = false;
            
            if (!empty($entity->getUnionId()) && method_exists($bizUser, 'setIdentity') && method_exists($bizUser, 'getIdentity')) {
                if ($bizUser->getIdentity() !== $entity->getUnionId()) {
                    $bizUser->setIdentity($entity->getUnionId());
                    $needUpdate = true;
                }
            }
            
            if (!empty($entity->getNickName()) && method_exists($bizUser, 'setNickName') && method_exists($bizUser, 'getNickName')) {
                if (empty($bizUser->getNickName()) || $bizUser->getNickName() !== $entity->getNickName()) {
                    $bizUser->setNickName($entity->getNickName());
                    $needUpdate = true;
                }
            }
            
            if (!empty($entity->getAvatarUrl()) && method_exists($bizUser, 'setAvatar') && method_exists($bizUser, 'getAvatar')) {
                if (empty($bizUser->getAvatar()) || $bizUser->getAvatar() !== $entity->getAvatarUrl()) {
                    $bizUser->setAvatar($entity->getAvatarUrl());
                    $needUpdate = true;
                }
            }
            
            if ($needUpdate) {
                $this->getEntityManager()->persist($bizUser);
                $this->getEntityManager()->flush();
            }
        }

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

<?php

namespace WechatMiniProgramAuthBundle\Repository;

use BizUserBundle\Entity\BizUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\UserIDBundle\Model\SystemUser;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramBundle\WechatMiniProgramBundle;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly UserLoaderInterface $userLoader,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($registry, User::class);
    }

    /**
     * 查找指定系统用户相关的微信小程序用户
     */
    public function getBySysUser(UserInterface $sysUser): ?User
    {
        $user = $this->findOneBy(['user' => $sysUser]);
        if (!$user) {
            $user = $this->findOneBy(['openId' => $sysUser->getUserIdentifier()]);
        }
        if (!$user) {
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
        if ($wechatUser) {
            return $wechatUser;
        }

        if ($user->getIdentity()) {
            $wechatUser = $this->findOneBy([
                'unionId' => $user->getIdentity(),
            ]);
            if ($wechatUser) {
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
        if ($bizUser) {
            return $bizUser;
        }
        $bizUser = $this->userLoader->loadUserByIdentifier($entity->getOpenId());
        if (!$bizUser) {
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

        $this->entityManager->persist($bizUser);
        $this->entityManager->flush();

        return $bizUser;
    }
}

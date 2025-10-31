<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Service;

use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\UserIDBundle\Model\SystemUser;
use Tourze\UserServiceContracts\UserManagerInterface;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Repository\UserRepository;
use WechatMiniProgramBundle\WechatMiniProgramBundle;

class UserTransformService
{
    public function __construct(
        private readonly UserManagerInterface $userLoader,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * 微信用户归微信用户，在实际开发中，我们实际存储的一般是最上层的User，所以此处会有一个转换和同步
     */
    public function transformToSysUser(User $entity): UserInterface
    {
        $sysUser = $entity->getUser();
        if (null !== $sysUser) {
            return $sysUser;
        }
        $sysUser = $this->userLoader->loadUserByIdentifier($entity->getOpenId());
        if (null === $sysUser && null !== $entity->getUnionId()) {
            $sysUser = $this->userLoader->loadUserByIdentifier($entity->getUnionId());
        }

        if (null === $sysUser) {
            // 从环境变量获取默认值，确保类型安全
            $defaultNickName = $_ENV['WECHAT_MINI_PROGRAM_DEFAULT_USER_NICKNAME'] ?? '微信用户';
            $defaultAvatarUrl = $_ENV['WECHAT_MINI_PROGRAM_DEFAULT_USER_AVATAR_URL'] ?? WechatMiniProgramBundle::DEFAULT_AVATAR;

            // 确保类型正确
            $nickName = is_string($defaultNickName) ? $defaultNickName : '微信用户';
            $avatarUrl = is_string($defaultAvatarUrl) ? $defaultAvatarUrl : WechatMiniProgramBundle::DEFAULT_AVATAR;

            $sysUser = $this->userLoader->createUser(
                $entity->getOpenId(),
                nickName: $nickName,
                avatarUrl: $avatarUrl,
            );
            // 直接使用注入的实体管理器，避免调用受保护的方法
            $em = $this->userRepository->createQueryBuilder('u')->getEntityManager();
            $em->persist($sysUser);
            $em->flush();
        }

        return $sysUser;
    }

    /**
     * 查找对应的微信小程序用户
     */
    public function transformToWechatUser(UserInterface $user): ?User
    {
        if ($user instanceof SystemUser) {
            return null;
        }

        $wechatUser = $this->userRepository->findOneBy([
            'openId' => $user->getUserIdentifier(),
        ]);
        if (null !== $wechatUser) {
            return $wechatUser;
        }

        if (method_exists($user, 'getIdentity') && $user->getIdentity()) {
            $wechatUser = $this->userRepository->findOneBy([
                'unionId' => $user->getIdentity(),
            ]);
            if (null !== $wechatUser) {
                return $wechatUser;
            }
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Repository\UserRepository;

#[Autoconfigure(public: true)]
class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function createUser(MiniProgramInterface $miniProgram, string $openId, ?string $unionId = null): User
    {
        // 检查用户是否已经存在
        $existingUser = $this->userRepository->loadUserByOpenId($openId);
        if (null !== $existingUser) {
            // Repository 返回的实际上是 User 实体，这里进行类型断言
            assert($existingUser instanceof User);

            return $existingUser;
        }

        // 确保 miniProgram 已被持久化
        if (!$this->entityManager->contains($miniProgram)) {
            $this->entityManager->persist($miniProgram);
        }

        $user = new User();
        $user->setAccount($miniProgram);
        $user->setOpenId($openId);
        $user->setUnionId($unionId);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}

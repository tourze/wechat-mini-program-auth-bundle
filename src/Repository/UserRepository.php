<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use Tourze\WechatMiniProgramUserContracts\UserInterface as WechatMiniProgramUserInterface;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface as WechatMiniProgramUserLoaderInterface;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Exception\UserRepositoryException;

/**
 * @extends ServiceEntityRepository<User>
 */
#[AsAlias(id: WechatMiniProgramUserLoaderInterface::class)]
#[AsRepository(entityClass: User::class)]
class UserRepository extends ServiceEntityRepository implements WechatMiniProgramUserLoaderInterface
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, User::class);
    }

    /**
     * 查找指定系统用户相关的微信小程序用户
     */
    public function getBySysUser(UserInterface $sysUser): ?User
    {
        $user = $this->findOneBy(['user' => $sysUser]);
        if (null === $user) {
            $user = $this->findOneBy(['openId' => $sysUser->getUserIdentifier()]);
        }
        if (null === $user) {
            $user = $this->findOneBy(['unionId' => $sysUser->getUserIdentifier()]);
        }

        return $user;
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
        throw new UserRepositoryException('createUser method should not be called on Repository. Use UserService instead.');
    }

    public function save(User $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

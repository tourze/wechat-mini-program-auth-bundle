<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Service;

use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;
use WechatMiniProgramAuthBundle\Entity\User;

/**
 * 测试用的 UserLoaderInterface 实现
 */
class TestUserLoader implements UserLoaderInterface
{
    /** @var array<string, User> */
    private array $users = [];

    public function addUser(User $user): void
    {
        $this->users[$user->getOpenId()] = $user;
    }

    public function loadUserByOpenId(string $openId): ?UserInterface
    {
        return $this->users[$openId] ?? null;
    }

    public function loadUserByUnionId(string $unionId): ?UserInterface
    {
        foreach ($this->users as $user) {
            if ($user->getUnionId() === $unionId) {
                return $user;
            }
        }

        return null;
    }

    public function createUser(MiniProgramInterface $miniProgram, string $openId, ?string $unionId = null): UserInterface
    {
        $user = new User();
        $user->setOpenId($openId);
        $user->setUnionId($unionId);
        $user->setAccount($miniProgram);

        $this->addUser($user);

        return $user;
    }

    public function clearUsers(): void
    {
        $this->users = [];
    }
}

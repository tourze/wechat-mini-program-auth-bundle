<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Procedure;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use WechatMiniProgramAuthBundle\Repository\UserRepository;

#[MethodTag(name: '微信小程序')]
#[MethodDoc(summary: '获取微信小程序用户信息')]
#[MethodExpose(method: 'GetCurrentWechatMiniProgramUser')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
class GetCurrentWechatMiniProgramUser extends LockableProcedure
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly Security $security,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $sysUser = $this->security->getUser();
        if (null === $sysUser) {
            throw new ApiException('用户未登录');
        }
        $user = $this->userRepository->getBySysUser($sysUser);
        if (null === $user) {
            throw new ApiException('找不到微信小程序用户信息');
        }

        return [
            'open_id' => $user->getOpenId(),
            'union_id' => $user->getUnionId(),
        ];
    }
}

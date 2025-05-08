<?php

namespace WechatMiniProgramAuthBundle\Procedure;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use WechatMiniProgramAuthBundle\Repository\UserRepository;

#[MethodTag('微信小程序')]
#[MethodDoc('获取用户信息')]
#[MethodExpose('GetCurrentWechatMiniProgramUser')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class GetCurrentWechatMiniProgramUser extends LockableProcedure
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly Security $security,
    ) {
    }

    public function execute(): array
    {
        $user = $this->userRepository->getBySysUser($this->security->getUser());
        if (!$user) {
            throw new ApiException('找不到微信小程序用户信息');
        }

        return [
            'open_id' => $user->getOpenId(),
            'union_id' => $user->getUnionId(),
        ];
    }
}

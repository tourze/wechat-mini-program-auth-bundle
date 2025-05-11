<?php

namespace WechatMiniProgramAuthBundle\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;

/**
 * 因为现在在 code2session 时就必然当做登录处理了，那么这里就肯定要登录啦。。
 *
 * @see https://developers.weixin.qq.com/miniprogram/dev/framework/open-ability/authorize.html
 */
#[MethodTag('微信小程序')]
#[MethodDoc('上报用户授权scope结果')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[MethodExpose('ReportWechatMiniProgramAuthorizeResult')]
#[Log]
class ReportWechatMiniProgramAuthorizeResult extends LockableProcedure
{
    #[MethodParam('已授权scope列表')]
    public array $scopes;

    public function __construct(
        private readonly UserLoaderInterface $userLoader,
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    public function execute(): array
    {
        $user = $this->userLoader->loadUserByOpenId($this->security->getUser()->getUserIdentifier());
        if (!$user) {
            throw new ApiException('找不到微信小程序用户信息');
        }

        $user->setAuthorizeScopes($this->scopes);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return [
            'id' => $user->getId(),
        ];
    }

    public static function getMockResult(): ?array
    {
        return [
            'id' => 456,
        ];
    }
}

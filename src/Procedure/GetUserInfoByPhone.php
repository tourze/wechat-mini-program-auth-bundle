<?php

namespace WechatMiniProgramAuthBundle\Procedure;

use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use WechatMiniProgramAuthBundle\Repository\UserRepository;

#[MethodTag('微信小程序')]
#[MethodDoc('通过手机号获取用户信息')]
#[MethodExpose('GetUserInfoByPhone')]
class GetUserInfoByPhone extends BaseProcedure
{
    #[MethodParam('phone')]
    public string $phoneNumber = '';

    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function execute(): array
    {
        if (empty($this->phoneNumber)) {
            throw new ApiException('请求参数不正确');
        }

        $user = $this->userRepository->createQueryBuilder('u')
            ->leftJoin('u.phoneNumbers', 'p')
            ->where('p.phoneNumber = :phoneNumber')
            ->setParameter('phoneNumber', $this->phoneNumber)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$user) {
            return [];
        }

        return [
            'open_id' => $user->getOpenId(),
            'union_id' => $user->getUnionId(),
            'phone' => $this->phoneNumber,
        ];
    }
}

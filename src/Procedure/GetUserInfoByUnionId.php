<?php

namespace WechatMiniProgramAuthBundle\Procedure;

use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramAuthBundle\Repository\PhoneNumberRepository;

#[MethodTag(name: '微信小程序')]
#[MethodDoc(summary: '通过unionId获取用户信息')]
#[MethodExpose(method: 'GetUserInfoByUnionId')]
class GetUserInfoByUnionId extends BaseProcedure
{
    #[MethodParam(description: 'unionId')]
    public string $unionId = '';

    public function __construct(
        private readonly UserLoaderInterface $userLoader,
        private readonly PhoneNumberRepository $phoneNumberRepository,
    ) {
    }

    public function execute(): array
    {
        if (empty($this->unionId)) {
            throw new ApiException('请求参数不正确');
        }

        $user = $this->userLoader->loadUserByUnionId($this->unionId);
        if ($user === null) {
            return [];
        }

        $phone = $this->phoneNumberRepository->createQueryBuilder('p')
            ->leftJoin('p.users', 'u')
            ->setMaxResults(1)
            ->where('u.unionId = :unionId')
            ->setParameter('unionId', $this->unionId)
            ->getQuery()
            ->getOneOrNullResult();

        return [
            'open_id' => $user->getOpenId(),
            'union_id' => $user->getUnionId(),
            'phone' => $phone instanceof PhoneNumber ? $phone->getPhoneNumber() : '',
        ];
    }
}

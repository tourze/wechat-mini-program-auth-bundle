<?php

namespace WechatMiniProgramAuthBundle\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\UserIDBundle\Model\SystemUser;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramAuthBundle\Event\GetPhoneNumberEvent;
use WechatMiniProgramAuthBundle\Repository\PhoneNumberRepository;
use WechatMiniProgramAuthBundle\Repository\UserRepository;

#[MethodTag('微信小程序')]
#[MethodDoc('更新用户手机号')]
#[MethodExpose('UploadUserPhone')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Log]
class UploadUserPhone extends LockableProcedure
{
    #[MethodParam('phone')]
    public string $phoneNumber = '';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PhoneNumberRepository $phoneNumberRepository,
        private readonly Security $security,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $user = $this->userRepository->getBySysUser($this->security->getUser());
        if ($user === null) {
            throw new ApiException('找不到微信小程序用户信息');
        }

        if (empty($this->phoneNumber)) {
            throw new ApiException('请求参数不正确');
        }

        // 保存手机号码
        $phoneNumber = $this->phoneNumberRepository->findOneBy([
            'phoneNumber' => $this->phoneNumber,
        ]);
        if ($phoneNumber === null) {
            $phoneNumber = new PhoneNumber();
            $phoneNumber->setPhoneNumber($this->phoneNumber);
        }

        $phoneNumber->setPurePhoneNumber($this->phoneNumber);
        $phoneNumber->setCountryCode('86');
        $phoneNumber->setWatermark([$this->phoneNumber]);
        $phoneNumber->setRawData($this->phoneNumber);

        $event = new GetPhoneNumberEvent();
        $event->setSender($this->security->getUser());
        $event->setReceiver(SystemUser::instance());
        $event->setPhoneNumber($phoneNumber);
        $event->setWechatUser($user);
        $event->setSource('');
        $event->setLaunchOptions(['query' => []]);
        $event->setEnterOptions(['query' => []]);
        $event->setResult([]);
        $this->eventDispatcher->dispatch($event);

        // 改成最终事件都OK了，我们才存数据库
        $phoneNumber->addUser($user);
        $this->entityManager->persist($phoneNumber);
        $this->entityManager->flush();

        return [
            'message' => '更新成功',
        ];
    }
}

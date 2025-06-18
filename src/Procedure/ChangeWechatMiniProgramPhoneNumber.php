<?php

namespace WechatMiniProgramAuthBundle\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
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
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramAuthBundle\Event\ChangePhoneNumberEvent;
use WechatMiniProgramAuthBundle\Exception\DecryptException;
use WechatMiniProgramAuthBundle\Repository\PhoneNumberRepository;
use WechatMiniProgramAuthBundle\Service\EncryptService;
use Yiisoft\Json\Json;

#[MethodTag('微信小程序')]
#[MethodDoc('修改用户手机号码')]
#[MethodExpose('ChangeWechatMiniProgramPhoneNumber')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Log]
#[WithMonologChannel('procedure')]
class ChangeWechatMiniProgramPhoneNumber extends LockableProcedure
{
    #[MethodParam('当前sessionKey')]
    public string $sessionKey = '';

    #[MethodParam('加密数据')]
    public string $encryptedData = '';

    #[MethodParam('向量值')]
    public string $iv = '';

    public function __construct(
        private readonly UserLoaderInterface $userLoader,
        private readonly PhoneNumberRepository $phoneNumberRepository,
        private readonly EncryptService $encryptService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Security $security,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(): array
    {
        $bizUser = $this->security->getUser();
        $wechatUser = $this->userLoader->loadUserByOpenId($bizUser->getUserIdentifier());
        if ($wechatUser === null) {
            throw new ApiException('找不到微信小程序用户信息');
        }

        try {
            // 旧的获取手机号码方式 https://developers.weixin.qq.com/miniprogram/dev/framework/open-ability/deprecatedGetPhoneNumber.html
            $res = $this->encryptService->decryptData($this->sessionKey, $this->iv, $this->encryptedData);
            $this->logger->debug('旧方式解密手机数据', [
                'res' => $res,
            ]);
        } catch (DecryptException $exception) {
            $this->logger->error('旧方式解密手机失败', [
                'exception' => $exception,
                'sessionKey' => $this->sessionKey,
                'iv' => $this->iv,
                'encryptedData' => $this->encryptedData,
            ]);
            throw new ApiException('找不到手机号码，请重试', 0, [], $exception);
        }

        // 保存手机号码
        $phoneNumber = $this->phoneNumberRepository->findOneBy([
            'phoneNumber' => $res['phoneNumber'],
        ]);
        if ($phoneNumber === null) {
            $phoneNumber = new PhoneNumber();
            $phoneNumber->setPhoneNumber($res['phoneNumber']);
        }

        $phoneNumber->setPurePhoneNumber($res['purePhoneNumber']);
        $phoneNumber->setCountryCode($res['countryCode']);
        $phoneNumber->setWatermark($res['watermark']);
        $phoneNumber->setRawData(Json::encode($res));

        $result = [
            'phoneNumber' => $phoneNumber->getPhoneNumber(),
            '__message' => '您已成功修改手机号 ！',
            '__showToast' => '您已成功修改手机号 ！',
        ];

        $event = new ChangePhoneNumberEvent();
        $event->setSender($bizUser);
        $event->setReceiver(SystemUser::instance());
        $event->setMessage("修改手机号码为：{$phoneNumber->getPhoneNumber()}");
        $event->setPhoneNumber($phoneNumber);
        $event->setWechatUser($wechatUser);
        $event->setResult($result);
        $this->eventDispatcher->dispatch($event);

        // 基于事件的处理结果做判断
        $result = $event->getResult();
        if ((bool) isset($result['fail'])) {
            throw new ApiException($result['__message']);
        }

        // 事件处理成功再保存
        $phoneNumber->addUser($wechatUser);
        $this->entityManager->persist($phoneNumber);
        $this->entityManager->flush();

        return $result;
    }
}

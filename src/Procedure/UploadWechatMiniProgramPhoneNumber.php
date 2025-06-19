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
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\JsonRPCLogBundle\Procedure\LogFormatProcedure;
use Tourze\UserIDBundle\Model\SystemUser;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Event\GetPhoneNumberEvent;
use WechatMiniProgramAuthBundle\Repository\PhoneNumberRepository;
use WechatMiniProgramAuthBundle\Request\GetUserPhoneNumberRequest;
use WechatMiniProgramBundle\Procedure\LaunchOptionsAware;
use WechatMiniProgramBundle\Service\Client;
use Yiisoft\Json\Json;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/phonenumber/phonenumber.getPhoneNumber.html
 * @see https://developers.weixin.qq.com/miniprogram/dev/framework/open-ability/getPhoneNumber.html
 */
#[MethodTag('微信小程序')]
#[MethodDoc('上传用户手机号码')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Log]
#[MethodExpose('UploadWechatMiniProgramPhoneNumber')]
#[WithMonologChannel('procedure')]
class UploadWechatMiniProgramPhoneNumber extends LockableProcedure implements LogFormatProcedure
{
    use LaunchOptionsAware;

    #[MethodParam('getPhoneNumber的授权code')]
    public string $code = '';

    #[MethodParam('注册来源')]
    public string $source = '';

    public function __construct(
        private readonly UserLoaderInterface $userLoader,
        private readonly PhoneNumberRepository $phoneNumberRepository,
        private readonly Client $client,
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

        if (!$wechatUser instanceof User) {
            throw new ApiException('用户类型不正确');
        }

        if (empty($this->code)) {
            throw new ApiException('已不支持旧方式获取手机号码，请升级微信版本');
        }

        $request = new GetUserPhoneNumberRequest();
        $request->setAccount($wechatUser->getAccount());
        $request->setCode($this->code);
        $res = $this->client->request($request);
        $this->logger->debug('远程获取微信手机号码信息', $res);
        if (!isset($res['phone_info'])) {
            throw new ApiException('找不到手机号码信息');
        }

        $res = $res['phone_info'];

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
        $phoneNumber->setLaunchOptions($this->launchOptions);
        $phoneNumber->setEnterOptions($this->enterOptions);

        $result = [
            'phoneNumber' => $phoneNumber->getPhoneNumber(),
            // '__message' => '恭喜你，授权注册成功 ！',
        ];

        $event = new GetPhoneNumberEvent();
        $event->setSender($bizUser);
        $event->setReceiver(SystemUser::instance());
        $event->setPhoneNumber($phoneNumber);
        $event->setWechatUser($wechatUser);
        $event->setSource($this->source);
        $event->setLaunchOptions($this->launchOptions);
        $event->setEnterOptions($this->enterOptions);
        $event->setResult($result);
        $this->eventDispatcher->dispatch($event);

        // 改成最终事件都OK了，我们才存数据库
        $phoneNumber->addUser($wechatUser);
        $this->entityManager->persist($phoneNumber);
        $this->entityManager->flush();

        return $event->getResult();
    }

    public function generateFormattedLogText(JsonRpcRequest $request): string
    {
        return '授权微信小程序手机号码';
    }
}

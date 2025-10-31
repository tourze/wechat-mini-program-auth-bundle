<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
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
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Procedure\LaunchOptionsAware;
use WechatMiniProgramBundle\Service\Client;
use Yiisoft\Json\Json;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/phonenumber/phonenumber.getPhoneNumber.html
 * @see https://developers.weixin.qq.com/miniprogram/dev/framework/open-ability/getPhoneNumber.html
 */
#[MethodTag(name: '微信小程序')]
#[MethodDoc(summary: '上传用户手机号码')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
#[Log]
#[MethodExpose(method: 'UploadWechatMiniProgramPhoneNumber')]
#[WithMonologChannel(channel: 'procedure')]
class UploadWechatMiniProgramPhoneNumber extends LockableProcedure implements LogFormatProcedure
{
    use LaunchOptionsAware;

    #[MethodParam(description: 'getPhoneNumber的授权code')]
    public string $code = '';

    /**
     * @var string 这里应该换成UTM风格的参数
     */
    #[MethodParam(description: '注册来源')]
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

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $bizUser = $this->security->getUser();
        if (null === $bizUser) {
            throw new ApiException('用户未登录');
        }

        $wechatUser = $this->getValidatedWechatUser($bizUser);
        $phoneInfo = $this->fetchPhoneInfo($wechatUser);
        $phoneNumber = $this->savePhoneNumber($phoneInfo);

        $result = [
            'phoneNumber' => $phoneNumber->getPhoneNumber(),
        ];

        $event = $this->createAndDispatchEvent($bizUser, $wechatUser, $phoneNumber, $result);

        $phoneNumber->addUser($wechatUser);
        $this->entityManager->persist($phoneNumber);
        $this->entityManager->flush();

        return $event->getResult();
    }

    private function getValidatedWechatUser(UserInterface $bizUser): User
    {
        $wechatUser = $this->userLoader->loadUserByOpenId($bizUser->getUserIdentifier());
        if (null === $wechatUser) {
            throw new ApiException('找不到微信小程序用户信息');
        }

        if (!$wechatUser instanceof User) {
            throw new ApiException('用户类型不正确');
        }

        if ('' === $this->code) {
            throw new ApiException('已不支持旧方式获取手机号码，请升级微信版本');
        }

        $account = $wechatUser->getAccount();
        if (null === $account) {
            throw new ApiException('该用户没有绑定微信小程序');
        }

        if (!$account instanceof Account) {
            throw new ApiException('账户类型不正确');
        }

        return $wechatUser;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchPhoneInfo(User $wechatUser): array
    {
        $account = $wechatUser->getAccount();
        if (null === $account) {
            throw new ApiException('账户信息缺失');
        }

        $request = new GetUserPhoneNumberRequest();
        $request->setAccount($account);
        $request->setCode($this->code);
        /** @var array<string, mixed> $res */
        $res = $this->client->request($request);
        $this->logger->debug('远程获取微信手机号码信息', ['response' => $res]);

        if (!isset($res['phone_info']) || !is_array($res['phone_info'])) {
            throw new ApiException('找不到手机号码信息');
        }

        /** @var array<string, mixed> $phoneInfo */
        $phoneInfo = $res['phone_info'];

        if (!isset($phoneInfo['phoneNumber']) || !is_string($phoneInfo['phoneNumber'])) {
            throw new ApiException('手机号码格式不正确');
        }

        $phoneInfo['rawData'] = Json::encode($res);

        return $phoneInfo;
    }

    /**
     * @param array<string, mixed> $phoneInfo
     */
    private function savePhoneNumber(array $phoneInfo): PhoneNumber
    {
        // 确保 phoneNumber 字段存在且是字符串
        if (!isset($phoneInfo['phoneNumber']) || !is_string($phoneInfo['phoneNumber'])) {
            throw new ApiException('手机号码字段缺失或类型错误');
        }

        $phoneNumber = $this->phoneNumberRepository->findOneBy([
            'phoneNumber' => $phoneInfo['phoneNumber'],
        ]);
        if (null === $phoneNumber) {
            $phoneNumber = new PhoneNumber();
            $phoneNumber->setPhoneNumber($phoneInfo['phoneNumber']);
        }

        $phoneNumber->setPurePhoneNumber(
            isset($phoneInfo['purePhoneNumber']) && is_string($phoneInfo['purePhoneNumber'])
                ? $phoneInfo['purePhoneNumber']
                : null
        );
        $phoneNumber->setCountryCode(
            isset($phoneInfo['countryCode']) && is_string($phoneInfo['countryCode'])
                ? $phoneInfo['countryCode']
                : null
        );

        // 处理 watermark 字段，确保是 array<string, mixed> 类型
        $watermark = null;
        if (isset($phoneInfo['watermark']) && is_array($phoneInfo['watermark'])) {
            /** @var array<string, mixed> $watermark */
            $watermark = $phoneInfo['watermark'];
        }
        $phoneNumber->setWatermark($watermark);

        // 确保 rawData 是字符串
        $rawData = isset($phoneInfo['rawData']) && is_string($phoneInfo['rawData'])
            ? $phoneInfo['rawData']
            : null;
        $phoneNumber->setRawData($rawData);
        $phoneNumber->setLaunchOptions($this->launchOptions);
        $phoneNumber->setEnterOptions($this->enterOptions);

        return $phoneNumber;
    }

    /**
     * @param array<string, mixed> $result
     */
    private function createAndDispatchEvent(UserInterface $bizUser, User $wechatUser, PhoneNumber $phoneNumber, array $result): GetPhoneNumberEvent
    {
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

        return $event;
    }

    public function generateFormattedLogText(JsonRpcRequest $request): string
    {
        return '授权微信小程序手机号码';
    }
}

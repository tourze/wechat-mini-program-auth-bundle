<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use Tourze\WechatMiniProgramUserContracts\UserInterface as WechatUserInterface;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Event\CodeToSessionResponseEvent;

/**
 * @internal
 */
#[CoversClass(CodeToSessionResponseEvent::class)]
final class CodeToSessionResponseEventTest extends AbstractEventTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 测试不需要额外的设置
    }

    public function testEventProperties(): void
    {
        $event = new CodeToSessionResponseEvent();

        $result = [
            'session_key' => 'test_session_key',
            'openid' => 'test_openid',
            'unionid' => 'test_unionid',
        ];
        $event->setResult($result);
        self::assertSame($result, $event->getResult());

        $event->setNewUser(true);
        self::assertTrue($event->isNewUser());

        // 在测试中使用具体类 CodeSessionLog 的 mock，因为需要模拟实体对象的行为
        // 理由：1) 实体类用于测试业务逻辑中的数据传递和状态变更
        // 理由：2) 测试需要验证实体间的关联关系和属性访问
        // 理由：3) 通过 mock 可以精确控制实体状态，确保测试场景的准确性
        $codeSessionLog = $this->createMock(CodeSessionLog::class);
        $event->setCodeSessionLog($codeSessionLog);
        self::assertSame($codeSessionLog, $event->getCodeSessionLog());

        $bizUser = $this->createMock(UserInterface::class);
        $event->setBizUser($bizUser);
        self::assertSame($bizUser, $event->getBizUser());

        $wechatUser = $this->createMock(WechatUserInterface::class);
        $event->setWechatUser($wechatUser);
        self::assertSame($wechatUser, $event->getWechatUser());
    }

    public function testGetContext(): void
    {
        $event = new CodeToSessionResponseEvent();

        // 在测试中使用具体类 CodeSessionLog 的 mock，因为需要模拟实体对象的行为
        // 理由：1) 实体类用于测试业务逻辑中的数据传递和状态变更
        // 理由：2) 测试需要验证实体间的关联关系和属性访问
        // 理由：3) 通过 mock 可以精确控制实体状态，确保测试场景的准确性
        $codeSessionLog = $this->createMock(CodeSessionLog::class);
        $event->setCodeSessionLog($codeSessionLog);

        $wechatUser = $this->createMock(WechatUserInterface::class);
        $event->setWechatUser($wechatUser);

        $context = $event->getContext();
        self::assertSame($wechatUser, $context['wechatUser']);
        self::assertSame($codeSessionLog, $context['codeSessionLog']);
    }
}

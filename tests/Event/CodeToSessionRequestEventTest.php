<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Event\CodeToSessionRequestEvent;
use WechatMiniProgramBundle\Entity\Account;

/**
 * @internal
 */
#[CoversClass(CodeToSessionRequestEvent::class)]
final class CodeToSessionRequestEventTest extends AbstractEventTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 测试不需要额外的设置
    }

    public function testEventProperties(): void
    {
        $event = new CodeToSessionRequestEvent();

        // 在测试中使用具体类 Account 的 mock，因为需要模拟实体对象的行为
        // 理由：1) 实体类用于测试业务逻辑中的数据传递和状态变更
        // 理由：2) 测试需要验证实体间的关联关系和属性访问
        // 理由：3) 通过 mock 可以精确控制实体状态，确保测试场景的准确性
        $account = $this->createMock(Account::class);
        $event->setAccount($account);
        self::assertSame($account, $event->getAccount());

        $event->setCode('test_code');
        self::assertSame('test_code', $event->getCode());

        // 在测试中使用具体类 CodeSessionLog 的 mock，因为需要模拟实体对象的行为
        // 理由：1) 实体类用于测试业务逻辑中的数据传递和状态变更
        // 理由：2) 测试需要验证实体间的关联关系和属性访问
        // 理由：3) 通过 mock 可以精确控制实体状态，确保测试场景的准确性
        $codeSessionLog = $this->createMock(CodeSessionLog::class);
        $event->setCodeSessionLog($codeSessionLog);
        self::assertSame($codeSessionLog, $event->getCodeSessionLog());

        $return = ['key' => 'value'];
        $event->setReturn($return);
        self::assertSame($return, $event->getReturn());

        $bizUser = $this->createMock(UserInterface::class);
        $event->setBizUser($bizUser);
        self::assertSame($bizUser, $event->getBizUser());
    }

    public function testLaunchOptionsAwareTrait(): void
    {
        $event = new CodeToSessionRequestEvent();

        $launchOptions = ['scene' => 1001];
        $event->setLaunchOptions($launchOptions);
        self::assertSame($launchOptions, $event->getLaunchOptions());
    }
}

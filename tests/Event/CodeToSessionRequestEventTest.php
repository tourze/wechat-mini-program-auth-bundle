<?php

namespace WechatMiniProgramAuthBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Event\CodeToSessionRequestEvent;
use WechatMiniProgramBundle\Entity\Account;

class CodeToSessionRequestEventTest extends TestCase
{
    public function testEventProperties(): void
    {
        $event = new CodeToSessionRequestEvent();
        
        $account = $this->createMock(Account::class);
        $event->setAccount($account);
        self::assertSame($account, $event->getAccount());
        
        $event->setCode('test_code');
        self::assertSame('test_code', $event->getCode());
        
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
<?php

namespace WechatMiniProgramAuthBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\WechatMiniProgramUserContracts\UserInterface as WechatUserInterface;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Event\CodeToSessionResponseEvent;

class CodeToSessionResponseEventTest extends TestCase
{
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
        
        $codeSessionLog = $this->createMock(CodeSessionLog::class);
        $event->setCodeSessionLog($codeSessionLog);
        
        $wechatUser = $this->createMock(WechatUserInterface::class);
        $event->setWechatUser($wechatUser);
        
        $context = $event->getContext();
        self::assertSame($wechatUser, $context['wechatUser']);
        self::assertSame($codeSessionLog, $context['codeSessionLog']);
    }
}
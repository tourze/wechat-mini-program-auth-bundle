<?php

namespace WechatMiniProgramAuthBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\TextManageBundle\Service\TextFormatter;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Repository\UserRepository;
use WechatMiniProgramAuthBundle\Service\WechatTextFormatter;

class WechatTextFormatterTest extends TestCase
{
    private WechatTextFormatter $formatter;
    private TextFormatter $innerFormatter;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->innerFormatter = $this->createMock(TextFormatter::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->formatter = new WechatTextFormatter($this->innerFormatter, $this->userRepository);
    }

    public function testFormatText_withNoWechatUser()
    {
        $text = 'Hello {name}';
        $params = ['name' => 'World'];
        $expectedText = 'Hello World';
        
        $this->innerFormatter->expects($this->once())
            ->method('formatText')
            ->with($text, $params)
            ->willReturn($expectedText);
            
        $result = $this->formatter->formatText($text, $params);
        $this->assertEquals($expectedText, $result);
    }

    public function testFormatText_withWechatUser()
    {
        $text = 'Hello {name}, your openId is {wechatMiniProgram:openId} and unionId is {wechatMiniProgram:unionId}';
        $user = $this->createMock(UserInterface::class);
        $params = ['name' => 'World', 'user' => $user];
        $innerResult = 'Hello World, your openId is {wechatMiniProgram:openId} and unionId is {wechatMiniProgram:unionId}';
        
        $wechatUser = $this->createMock(User::class);
        $wechatUser->method('getOpenId')->willReturn('test_open_id');
        $wechatUser->method('getUnionId')->willReturn('test_union_id');
        
        $this->innerFormatter->expects($this->once())
            ->method('formatText')
            ->with($text, $params)
            ->willReturn($innerResult);
            
        $this->userRepository->expects($this->once())
            ->method('transformToWechatUser')
            ->with($user)
            ->willReturn($wechatUser);
            
        $expectedText = 'Hello World, your openId is test_open_id and unionId is test_union_id';
        $result = $this->formatter->formatText($text, $params);
        $this->assertEquals($expectedText, $result);
    }

    public function testFormatText_withWechatUserNotFound()
    {
        $text = 'Hello {name}, your openId is {wechatMiniProgram:openId}';
        $user = $this->createMock(UserInterface::class);
        $params = ['name' => 'World', 'user' => $user];
        $innerResult = 'Hello World, your openId is {wechatMiniProgram:openId}';
        
        $this->innerFormatter->expects($this->once())
            ->method('formatText')
            ->with($text, $params)
            ->willReturn($innerResult);
            
        $this->userRepository->expects($this->once())
            ->method('transformToWechatUser')
            ->with($user)
            ->willReturn(null);
            
        $result = $this->formatter->formatText($text, $params);
        $this->assertEquals($innerResult, $result);
    }

    public function testFormatText_withMultipleWechatPlaceholders()
    {
        $text = '{wechatMiniProgram:openId}';
        $user = $this->createMock(UserInterface::class);
        $params = ['user' => $user];
        $innerResult = '{wechatMiniProgram:openId}';
        
        $wechatUser = $this->createMock(User::class);
        $wechatUser->method('getOpenId')->willReturn('test_open_id');
        $wechatUser->method('getUnionId')->willReturn('');
        
        $this->innerFormatter->expects($this->once())
            ->method('formatText')
            ->with($text, $params)
            ->willReturn($innerResult);
            
        $this->userRepository->expects($this->once())
            ->method('transformToWechatUser')
            ->with($user)
            ->willReturn($wechatUser);
            
        $expectedText = 'test_open_id';
        $result = $this->formatter->formatText($text, $params);
        $this->assertEquals($expectedText, $result);
    }
    
    /**
     * 测试当参数未实现UserInterface时的处理
     */
    public function testFormatText_withNonUserInterfaceParameter()
    {
        $text = 'Hello {name}, your openId is {wechatMiniProgram:openId}';
        $nonUser = new \stdClass();
        $params = ['name' => 'World', 'user' => $nonUser];
        $innerResult = 'Hello World, your openId is {wechatMiniProgram:openId}';
        
        $this->innerFormatter->expects($this->once())
            ->method('formatText')
            ->with($text, $params)
            ->willReturn($innerResult);
            
        // 由于传入的不是UserInterface实例，transformToWechatUser不应被调用
        $this->userRepository->expects($this->never())
            ->method('transformToWechatUser');
            
        $result = $this->formatter->formatText($text, $params);
        $this->assertEquals($innerResult, $result);
    }
    
    /**
     * 测试当没有user参数时的处理
     */
    public function testFormatText_withoutUserParameter()
    {
        $text = 'Hello {name}, your openId is {wechatMiniProgram:openId}';
        $params = ['name' => 'World']; // 没有user参数
        $innerResult = 'Hello World, your openId is {wechatMiniProgram:openId}';
        
        $this->innerFormatter->expects($this->once())
            ->method('formatText')
            ->with($text, $params)
            ->willReturn($innerResult);
            
        // 由于没有user参数，transformToWechatUser不应被调用
        $this->userRepository->expects($this->never())
            ->method('transformToWechatUser');
            
        $result = $this->formatter->formatText($text, $params);
        $this->assertEquals($innerResult, $result);
    }
} 
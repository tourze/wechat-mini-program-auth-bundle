<?php

namespace WechatMiniProgramAuthBundle\Tests\Unit\Enum;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramAuthBundle\Enum\Language;

class LanguageTest extends TestCase
{
    public function testLanguageValues()
    {
        $this->assertEquals('en', Language::en->value);
        $this->assertEquals('zh_CN', Language::zh_CN->value);
        $this->assertEquals('zh_TW', Language::zh_TW->value);
    }

    public function testLanguageLabels()
    {
        $this->assertEquals('en', Language::en->getLabel());
        $this->assertEquals('zh_CN', Language::zh_CN->getLabel());
        $this->assertEquals('zh_TW', Language::zh_TW->getLabel());
    }

    public function testLanguageCases()
    {
        $cases = Language::cases();
        $this->assertCount(3, $cases);
        $this->assertContains(Language::en, $cases);
        $this->assertContains(Language::zh_CN, $cases);
        $this->assertContains(Language::zh_TW, $cases);
    }

    public function testLanguageFromValue()
    {
        $this->assertEquals(Language::en, Language::from('en'));
        $this->assertEquals(Language::zh_CN, Language::from('zh_CN'));
        $this->assertEquals(Language::zh_TW, Language::from('zh_TW'));
    }

    public function testLanguageTryFromValue()
    {
        $this->assertEquals(Language::en, Language::tryFrom('en'));
        $this->assertEquals(Language::zh_CN, Language::tryFrom('zh_CN'));
        $this->assertEquals(Language::zh_TW, Language::tryFrom('zh_TW'));
        $this->assertNull(Language::tryFrom('invalid'));
    }

    public function testLanguageToSelectItem()
    {
        $item = Language::en->toSelectItem();
        $this->assertArrayHasKey('value', $item);
        $this->assertArrayHasKey('label', $item);
        $this->assertEquals('en', $item['value']);
        $this->assertEquals('en', $item['label']);
    }

    public function testLanguageToArray()
    {
        $array = Language::zh_CN->toArray();
        $this->assertArrayHasKey('value', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertEquals('zh_CN', $array['value']);
        $this->assertEquals('zh_CN', $array['label']);
    }

    public function testLanguageGenOptions()
    {
        $options = Language::genOptions();
        $this->assertCount(3, $options);
        
        foreach ($options as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
        }
    }
}
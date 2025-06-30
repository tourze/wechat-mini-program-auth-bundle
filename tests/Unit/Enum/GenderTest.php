<?php

namespace WechatMiniProgramAuthBundle\Tests\Unit\Enum;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramAuthBundle\Enum\Gender;

class GenderTest extends TestCase
{
    public function testGenderValues()
    {
        $this->assertEquals(0, Gender::UNKNOWN->value);
        $this->assertEquals(1, Gender::MALE->value);
        $this->assertEquals(2, Gender::FEMALE->value);
    }

    public function testGenderLabels()
    {
        $this->assertEquals('未知', Gender::UNKNOWN->getLabel());
        $this->assertEquals('男性', Gender::MALE->getLabel());
        $this->assertEquals('女性', Gender::FEMALE->getLabel());
    }

    public function testGenderCases()
    {
        $cases = Gender::cases();
        $this->assertCount(3, $cases);
        $this->assertContains(Gender::UNKNOWN, $cases);
        $this->assertContains(Gender::MALE, $cases);
        $this->assertContains(Gender::FEMALE, $cases);
    }

    public function testGenderFromValue()
    {
        $this->assertEquals(Gender::UNKNOWN, Gender::from(0));
        $this->assertEquals(Gender::MALE, Gender::from(1));
        $this->assertEquals(Gender::FEMALE, Gender::from(2));
    }

    public function testGenderTryFromValue()
    {
        $this->assertEquals(Gender::UNKNOWN, Gender::tryFrom(0));
        $this->assertEquals(Gender::MALE, Gender::tryFrom(1));
        $this->assertEquals(Gender::FEMALE, Gender::tryFrom(2));
        $this->assertNull(Gender::tryFrom(999));
    }

    public function testGenderToSelectItem()
    {
        $item = Gender::UNKNOWN->toSelectItem();
        $this->assertArrayHasKey('value', $item);
        $this->assertArrayHasKey('label', $item);
        $this->assertEquals(0, $item['value']);
        $this->assertEquals('未知', $item['label']);
    }

    public function testGenderToArray()
    {
        $array = Gender::MALE->toArray();
        $this->assertArrayHasKey('value', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertEquals(1, $array['value']);
        $this->assertEquals('男性', $array['label']);
    }

    public function testGenderGenOptions()
    {
        $options = Gender::genOptions();
        $this->assertCount(3, $options);
        
        foreach ($options as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
        }
    }
}
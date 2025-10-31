<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use WechatMiniProgramAuthBundle\Enum\Language;

/**
 * @internal
 */
#[CoversClass(Language::class)]
final class LanguageTest extends AbstractEnumTestCase
{
    #[TestWith([Language::en, 'en', 'en'])]
    #[TestWith([Language::zh_CN, 'zh_CN', 'zh_CN'])]
    #[TestWith([Language::zh_TW, 'zh_TW', 'zh_TW'])]
    public function testValueAndLabel(Language $language, string $expectedValue, string $expectedLabel): void
    {
        $this->assertEquals($expectedValue, $language->value);
        $this->assertEquals($expectedLabel, $language->getLabel());
    }

    public function testFromMethodExceptionHandling(): void
    {
        $this->expectException(\ValueError::class);
        Language::from('invalid_language');
    }

    #[TestWith(['en', Language::en])]
    #[TestWith(['zh_CN', Language::zh_CN])]
    #[TestWith(['zh_TW', Language::zh_TW])]
    #[TestWith(['invalid', null])]
    #[TestWith(['', null])]
    #[TestWith(['zh_HK', null])]
    public function testTryFromInvalidInput(string $value, ?Language $expected): void
    {
        $this->assertEquals($expected, Language::tryFrom($value));
    }

    public function testValueUniqueness(): void
    {
        $values = array_map(fn (Language $case) => $case->value, Language::cases());
        $this->assertEquals(array_unique($values), $values, 'All enum values must be unique');
    }

    public function testLabelUniqueness(): void
    {
        $labels = array_map(fn (Language $case) => $case->getLabel(), Language::cases());
        $this->assertEquals(array_unique($labels), $labels, 'All enum labels must be unique');
    }

    public function testToArrayStructure(): void
    {
        foreach (Language::cases() as $language) {
            $array = $language->toArray();
            $this->assertArrayHasKey('value', $array);
            $this->assertArrayHasKey('label', $array);
            $this->assertEquals($language->value, $array['value']);
            $this->assertEquals($language->getLabel(), $array['label']);
        }
    }

    public function testToSelectItemStructure(): void
    {
        foreach (Language::cases() as $language) {
            $item = $language->toSelectItem();
            $this->assertArrayHasKey('value', $item);
            $this->assertArrayHasKey('label', $item);
            $this->assertEquals($language->value, $item['value']);
            $this->assertEquals($language->getLabel(), $item['label']);
        }
    }

    public function testGenOptionsCompleteness(): void
    {
        $options = Language::genOptions();
        $expectedCount = count(Language::cases());

        $this->assertCount($expectedCount, $options);

        $optionValues = array_column($options, 'value');
        $enumValues = array_map(fn (Language $case) => $case->value, Language::cases());

        sort($optionValues);
        sort($enumValues);

        $this->assertEquals($enumValues, $optionValues, 'genOptions must include all enum values');
    }
}

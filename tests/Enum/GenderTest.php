<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use WechatMiniProgramAuthBundle\Enum\Gender;

/**
 * @internal
 */
#[CoversClass(Gender::class)]
final class GenderTest extends AbstractEnumTestCase
{
    #[TestWith([Gender::UNKNOWN, 0, '未知'])]
    #[TestWith([Gender::MALE, 1, '男性'])]
    #[TestWith([Gender::FEMALE, 2, '女性'])]
    public function testValueAndLabel(Gender $gender, int $expectedValue, string $expectedLabel): void
    {
        $this->assertEquals($expectedValue, $gender->value);
        $this->assertEquals($expectedLabel, $gender->getLabel());
    }

    public function testFromMethodExceptionHandling(): void
    {
        $this->expectException(\ValueError::class);
        Gender::from(999);
    }

    #[TestWith([0, Gender::UNKNOWN])]
    #[TestWith([1, Gender::MALE])]
    #[TestWith([2, Gender::FEMALE])]
    #[TestWith([999, null])]
    #[TestWith([-1, null])]
    public function testTryFromInvalidInput(int $value, ?Gender $expected): void
    {
        $this->assertEquals($expected, Gender::tryFrom($value));
    }

    public function testValueUniqueness(): void
    {
        $values = array_map(fn (Gender $case) => $case->value, Gender::cases());
        $this->assertEquals(array_unique($values), $values, 'All enum values must be unique');
    }

    public function testLabelUniqueness(): void
    {
        $labels = array_map(fn (Gender $case) => $case->getLabel(), Gender::cases());
        $this->assertEquals(array_unique($labels), $labels, 'All enum labels must be unique');
    }

    public function testToArrayStructure(): void
    {
        foreach (Gender::cases() as $gender) {
            $array = $gender->toArray();
            $this->assertArrayHasKey('value', $array);
            $this->assertArrayHasKey('label', $array);
            $this->assertEquals($gender->value, $array['value']);
            $this->assertEquals($gender->getLabel(), $array['label']);
        }
    }

    public function testToSelectItemStructure(): void
    {
        foreach (Gender::cases() as $gender) {
            $item = $gender->toSelectItem();
            $this->assertArrayHasKey('value', $item);
            $this->assertArrayHasKey('label', $item);
            $this->assertEquals($gender->value, $item['value']);
            $this->assertEquals($gender->getLabel(), $item['label']);
        }
    }

    public function testGenOptionsCompleteness(): void
    {
        $options = Gender::genOptions();
        $expectedCount = count(Gender::cases());

        $this->assertCount($expectedCount, $options);

        $optionValues = array_column($options, 'value');
        $enumValues = array_map(fn (Gender $case) => $case->value, Gender::cases());

        sort($optionValues);
        sort($enumValues);

        $this->assertEquals($enumValues, $optionValues, 'genOptions must include all enum values');
    }
}

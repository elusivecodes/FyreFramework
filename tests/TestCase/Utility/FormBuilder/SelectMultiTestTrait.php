<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FormBuilder;

use PHPUnit\Framework\Attributes\DataProvider;

trait SelectMultiTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function selectMultiAttributesProvider(): array
    {
        return [
            'array value' => [
                ['data-test' => [1, 2]],
                '<select data-test="[1,2]" multiple></select>',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<select data-test="&lt;test&gt;" multiple></select>',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<select class="test" multiple></select>',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'select'],
                '<select class="test" id="select" multiple></select>',
            ],
            'attribute order' => [
                ['id' => 'select', 'class' => 'test'],
                '<select class="test" id="select" multiple></select>',
            ],
        ];
    }

    /**
     * @return array<string, array{array<int|string, mixed>, string}>
     */
    public static function selectMultiOptionAttributesProvider(): array
    {
        return [
            'options attributes' => [
                [
                    ['value' => 'a', 'label' => 'A'],
                ],
                '<select multiple><option value="a">A</option></select>',
            ],
            'options attributes escape' => [
                [
                    ['value' => 'a', 'label' => 'A', 'data-test' => '<test>'],
                ],
                '<select multiple><option data-test="&lt;test&gt;" value="a">A</option></select>',
            ],
            'options attributes invalid' => [
                [
                    ['value' => 'a', 'label' => 'A', '*class*' => 'test'],
                ],
                '<select multiple><option class="test" value="a">A</option></select>',
            ],
        ];
    }

    /**
     * @return array<string, array{array<int|string, mixed>, string}>
     */
    public static function selectMultiOptionsProvider(): array
    {
        return [
            'option group' => [
                [
                    [
                        'label' => 'test',
                        'children' => ['A', 'B'],
                    ],
                ],
                '<select multiple><optgroup label="test"><option value="0">A</option><option value="1">B</option></optgroup></select>',
            ],
            'options' => [
                ['A', 'B'],
                '<select multiple><option value="0">A</option><option value="1">B</option></select>',
            ],
            'options assoc' => [
                ['a' => 'A'],
                '<select multiple><option value="a">A</option></select>',
            ],
        ];
    }

    public function testSelectMulti(): void
    {
        $this->assertSame(
            '<select multiple></select>',
            $this->form->selectMulti()
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('selectMultiAttributesProvider')]
    public function testSelectMultiAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->form->selectMulti(attributes: $attributes)
        );
    }

    public function testSelectMultiName(): void
    {
        $this->assertSame(
            '<select name="select" multiple></select>',
            $this->form->selectMulti('select')
        );
    }

    /**
     * @param array<int|string, mixed> $options
     */
    #[DataProvider('selectMultiOptionAttributesProvider')]
    public function testSelectMultiOptionAttributes(array $options, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->form->selectMulti(options: $options)
        );
    }

    /**
     * @param array<int|string, mixed> $options
     */
    #[DataProvider('selectMultiOptionsProvider')]
    public function testSelectMultiOptions(array $options, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->form->selectMulti(options: $options)
        );
    }

    public function testSelectMultiOptionsEscape(): void
    {
        $this->assertSame(
            '<select multiple><option value="0">&lt;test&gt;</option></select>',
            $this->form->selectMulti(options: [
                '<test>',
            ])
        );
    }

    public function testSelectMultiSelected(): void
    {
        $this->assertSame(
            '<select multiple><option value="0">A</option><option value="1" selected>B</option></select>',
            $this->form->selectMulti(attributes: [
                'value' => 1,
            ], options: [
                'A',
                'B',
            ])
        );
    }

    public function testSelectMultiSelectedArray(): void
    {
        $this->assertSame(
            '<select multiple><option value="0">A</option><option value="1" selected>B</option><option value="2" selected>C</option></select>',
            $this->form->selectMulti(attributes: [
                'value' => [1, 2],
            ], options: [
                'A',
                'B',
                'C',
            ])
        );
    }
}

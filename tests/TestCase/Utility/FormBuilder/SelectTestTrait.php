<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FormBuilder;

use PHPUnit\Framework\Attributes\DataProvider;

trait SelectTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function selectAttributesProvider(): array
    {
        return [
            'array value' => [
                ['data-test' => [1, 2]],
                '<select data-test="[1,2]"></select>',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<select data-test="&lt;test&gt;"></select>',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<select class="test"></select>',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'select'],
                '<select class="test" id="select"></select>',
            ],
            'attribute order' => [
                ['id' => 'select', 'class' => 'test'],
                '<select class="test" id="select"></select>',
            ],
        ];
    }

    /**
     * @return array<string, array{array<int|string, mixed>, string}>
     */
    public static function selectOptionAttributesProvider(): array
    {
        return [
            'options attributes' => [
                [
                    ['value' => 'a', 'label' => 'A'],
                ],
                '<select><option value="a">A</option></select>',
            ],
            'options attributes escape' => [
                [
                    ['value' => 'a', 'label' => 'A', 'data-test' => '<test>'],
                ],
                '<select><option data-test="&lt;test&gt;" value="a">A</option></select>',
            ],
            'options attributes invalid' => [
                [
                    ['value' => 'a', 'label' => 'A', '*class*' => 'test'],
                ],
                '<select><option class="test" value="a">A</option></select>',
            ],
        ];
    }

    /**
     * @return array<string, array{array<int|string, mixed>, string}>
     */
    public static function selectOptionsProvider(): array
    {
        return [
            'option group' => [
                [
                    [
                        'label' => 'test',
                        'children' => ['A', 'B'],
                    ],
                ],
                '<select><optgroup label="test"><option value="0">A</option><option value="1">B</option></optgroup></select>',
            ],
            'options' => [
                ['A', 'B'],
                '<select><option value="0">A</option><option value="1">B</option></select>',
            ],
            'options assoc' => [
                ['a' => 'A'],
                '<select><option value="a">A</option></select>',
            ],
        ];
    }

    public function testSelect(): void
    {
        $this->assertSame(
            '<select></select>',
            $this->form->select()
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('selectAttributesProvider')]
    public function testSelectAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->form->select(attributes: $attributes)
        );
    }

    public function testSelectName(): void
    {
        $this->assertSame(
            '<select name="select"></select>',
            $this->form->select('select')
        );
    }

    /**
     * @param array<int|string, mixed> $options
     */
    #[DataProvider('selectOptionAttributesProvider')]
    public function testSelectOptionAttributes(array $options, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->form->select(options: $options)
        );
    }

    /**
     * @param array<int|string, mixed> $options
     */
    #[DataProvider('selectOptionsProvider')]
    public function testSelectOptions(array $options, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->form->select(options: $options)
        );
    }

    public function testSelectOptionsEscape(): void
    {
        $this->assertSame(
            '<select><option value="0">&lt;test&gt;</option></select>',
            $this->form->select(options: [
                '<test>',
            ])
        );
    }

    public function testSelectSelected(): void
    {
        $this->assertSame(
            '<select><option value="0">A</option><option value="1" selected>B</option></select>',
            $this->form->select(attributes: [
                'value' => 1,
            ], options: [
                'A',
                'B',
            ])
        );
    }
}

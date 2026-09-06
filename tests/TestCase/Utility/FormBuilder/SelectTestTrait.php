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

    public function testSelectOptionGroup(): void
    {
        $this->assertSame(
            '<select><optgroup label="test"><option value="0">A</option><option value="1">B</option></optgroup></select>',
            $this->form->select(options: [
                [
                    'label' => 'test',
                    'children' => [
                        'A',
                        'B',
                    ],
                ],
            ])
        );
    }

    public function testSelectOptions(): void
    {
        $this->assertSame(
            '<select><option value="0">A</option><option value="1">B</option></select>',
            $this->form->select(options: [
                'A',
                'B',
            ])
        );
    }

    public function testSelectOptionsAssoc(): void
    {
        $this->assertSame(
            '<select><option value="a">A</option></select>',
            $this->form->select(options: [
                'a' => 'A',
            ])
        );
    }

    public function testSelectOptionsAttributes(): void
    {
        $this->assertSame(
            '<select><option value="a">A</option></select>',
            $this->form->select(options: [
                [
                    'value' => 'a',
                    'label' => 'A',
                ],
            ])
        );
    }

    public function testSelectOptionsAttributesEscape(): void
    {
        $this->assertSame(
            '<select><option data-test="&lt;test&gt;" value="a">A</option></select>',
            $this->form->select(options: [
                [
                    'value' => 'a',
                    'label' => 'A',
                    'data-test' => '<test>',
                ],
            ])
        );
    }

    public function testSelectOptionsAttributesInvalid(): void
    {
        $this->assertSame(
            '<select><option class="test" value="a">A</option></select>',
            $this->form->select(options: [
                [
                    'value' => 'a',
                    'label' => 'A',
                    '*class*' => 'test',
                ],
            ])
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

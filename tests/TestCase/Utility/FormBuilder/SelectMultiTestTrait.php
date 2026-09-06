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

    public function testSelectMultiOptionGroup(): void
    {
        $this->assertSame(
            '<select multiple><optgroup label="test"><option value="0">A</option><option value="1">B</option></optgroup></select>',
            $this->form->selectMulti(options: [
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

    public function testSelectMultiOptions(): void
    {
        $this->assertSame(
            '<select multiple><option value="0">A</option><option value="1">B</option></select>',
            $this->form->selectMulti(options: [
                'A',
                'B',
            ])
        );
    }

    public function testSelectMultiOptionsAssoc(): void
    {
        $this->assertSame(
            '<select multiple><option value="a">A</option></select>',
            $this->form->selectMulti(options: [
                'a' => 'A',
            ])
        );
    }

    public function testSelectMultiOptionsAttributes(): void
    {
        $this->assertSame(
            '<select multiple><option value="a">A</option></select>',
            $this->form->selectMulti(options: [
                [
                    'value' => 'a',
                    'label' => 'A',
                ],
            ])
        );
    }

    public function testSelectMultiOptionsAttributesEscape(): void
    {
        $this->assertSame(
            '<select multiple><option data-test="&lt;test&gt;" value="a">A</option></select>',
            $this->form->selectMulti(options: [
                [
                    'value' => 'a',
                    'label' => 'A',
                    'data-test' => '<test>',
                ],
            ])
        );
    }

    public function testSelectMultiOptionsAttributesInvalid(): void
    {
        $this->assertSame(
            '<select multiple><option class="test" value="a">A</option></select>',
            $this->form->selectMulti(options: [
                [
                    'value' => 'a',
                    'label' => 'A',
                    '*class*' => 'test',
                ],
            ])
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

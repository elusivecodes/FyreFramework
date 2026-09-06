<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use Closure;
use Fyre\View\View;
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
                '<input name="select" type="hidden" value="" /><select id="select" name="select[]" data-test="[1,2]" multiple></select>',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<input name="select" type="hidden" value="" /><select id="select" name="select[]" data-test="&lt;test&gt;" multiple></select>',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<input name="select" type="hidden" value="" /><select class="test" id="select" name="select[]" multiple></select>',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'other'],
                '<input name="select" type="hidden" value="" /><select class="test" id="other" name="select[]" multiple></select>',
            ],
            'attribute order' => [
                ['id' => 'other', 'class' => 'test'],
                '<input name="select" type="hidden" value="" /><select class="test" id="other" name="select[]" multiple></select>',
            ],
            'id' => [
                ['id' => 'other'],
                '<input name="select" type="hidden" value="" /><select id="other" name="select[]" multiple></select>',
            ],
            'id false' => [
                ['id' => false],
                '<input name="select" type="hidden" value="" /><select name="select[]" multiple></select>',
            ],
            'name' => [
                ['name' => 'other'],
                '<input name="other" type="hidden" value="" /><select id="select" name="other[]" multiple></select>',
            ],
            'name false' => [
                ['name' => false],
                '<select id="select" multiple></select>',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function selectMultiFieldNameProvider(): array
    {
        return [
            'flat' => [
                'select_value',
                '<input name="select_value" type="hidden" value="" /><select id="select-value" name="select_value[]" multiple></select>',
            ],
            'dotted' => [
                'key.select_value',
                '<input name="key[select_value]" type="hidden" value="" /><select id="key-select-value" name="key[select_value][]" multiple></select>',
            ],
            'deeply dotted' => [
                'deep.key.select_value',
                '<input name="deep[key][select_value]" type="hidden" value="" /><select id="deep-key-select-value" name="deep[key][select_value][]" multiple></select>',
            ],
        ];
    }

    /**
     * @return array<string, array{array<mixed>, string}>
     */
    public static function selectMultiOptionsProvider(): array
    {
        return [
            'indexed' => [
                [
                    'A',
                    'B',
                ],
                '<input name="select" type="hidden" value="" /><select id="select" name="select[]" multiple><option value="0">A</option><option value="1">B</option></select>',
            ],
            'associative' => [
                [
                    'a' => 'A',
                ],
                '<input name="select" type="hidden" value="" /><select id="select" name="select[]" multiple><option value="a">A</option></select>',
            ],
            'attribute arrays' => [
                [
                    [
                        'value' => 'a',
                        'label' => 'A',
                    ],
                ],
                '<input name="select" type="hidden" value="" /><select id="select" name="select[]" multiple><option value="a">A</option></select>',
            ],
            'grouped' => [
                [
                    [
                        'label' => 'test',
                        'children' => [
                            'A',
                            'B',
                        ],
                    ],
                ],
                '<input name="select" type="hidden" value="" /><select id="select" name="select[]" multiple><optgroup label="test"><option value="0">A</option><option value="1">B</option></optgroup></select>',
            ],
        ];
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function selectMultiSelectedPostProvider(): array
    {
        return [
            'flat' => [
                [
                    'select' => ['1', '2'],
                ],
                'select',
                '<input name="select" type="hidden" value="" /><select id="select" name="select[]" multiple><option value="0">A</option><option value="1" selected>B</option><option value="2" selected>C</option></select>',
            ],
            'dotted' => [
                [
                    'key' => [
                        'select' => ['1', '2'],
                    ],
                ],
                'key.select',
                '<input name="key[select]" type="hidden" value="" /><select id="key-select" name="key[select][]" multiple><option value="0">A</option><option value="1" selected>B</option><option value="2" selected>C</option></select>',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('selectMultiAttributesProvider')]
    public function testSelectMultiAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->selectMulti('select', $attributes)
        );
    }

    #[DataProvider('selectMultiFieldNameProvider')]
    public function testSelectMultiFieldName(string $field, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->selectMulti($field)
        );
    }

    public function testSelectMultiHiddenFieldFalse(): void
    {
        $this->assertSame(
            '<select id="select" name="select[]" multiple></select>',
            $this->view->Form->selectMulti('select', hiddenField: false)
        );
    }

    public function testSelectMultiIdPrefix(): void
    {
        $this->view->Form->open(idPrefix: 'test');

        $this->assertSame(
            '<input name="select" type="hidden" value="" /><select id="test-select" name="select[]" multiple></select>',
            $this->view->Form->selectMulti('select')
        );
    }

    public function testSelectMultiNamePrefix(): void
    {
        $this->assertSame(
            '<input name="select[]" type="hidden" value="" /><select id="select" name="select[]" multiple></select>',
            $this->view->Form->selectMulti('select', [
                'name' => 'select[]',
            ])
        );
    }

    /**
     * @param array<mixed> $options
     */
    #[DataProvider('selectMultiOptionsProvider')]
    public function testSelectMultiOptions(array $options, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->selectMulti('select', options: $options)
        );
    }

    public function testSelectMultiOptionsAttributesEscape(): void
    {
        $this->assertSame(
            '<input name="select" type="hidden" value="" /><select id="select" name="select[]" multiple><option data-test="&lt;test&gt;" value="a">A</option></select>',
            $this->view->Form->selectMulti('select', options: [
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
            '<input name="select" type="hidden" value="" /><select id="select" name="select[]" multiple><option class="test" value="a">A</option></select>',
            $this->view->Form->selectMulti('select', options: [
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
            '<input name="select" type="hidden" value="" /><select id="select" name="select[]" multiple><option value="0">&lt;test&gt;</option></select>',
            $this->view->Form->selectMulti('select', options: [
                '<test>',
            ])
        );
    }

    public function testSelectMultiSelected(): void
    {
        $this->assertSame(
            '<input name="select" type="hidden" value="" /><select id="select" name="select[]" multiple><option value="0">A</option><option value="1" selected>B</option></select>',
            $this->view->Form->selectMulti('select', [
                'value' => 1,
            ], [
                'A',
                'B',
            ])
        );
    }

    public function testSelectMultiSelectedArray(): void
    {
        $this->assertSame(
            '<input name="select" type="hidden" value="" /><select id="select" name="select[]" multiple><option value="0">A</option><option value="1" selected>B</option><option value="2" selected>C</option></select>',
            $this->view->Form->selectMulti('select', [
                'value' => [1, 2],
            ], [
                'A',
                'B',
                'C',
            ])
        );
    }

    public function testSelectMultiSelectedDefault(): void
    {
        $this->assertSame(
            '<input name="select" type="hidden" value="" /><select id="select" name="select[]" multiple><option value="0">A</option><option value="1" selected>B</option><option value="2" selected>C</option></select>',
            $this->view->Form->selectMulti('select', [
                'default' => ['1', '2'],
            ], [
                'A',
                'B',
                'C',
            ])
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('selectMultiSelectedPostProvider')]
    public function testSelectMultiSelectedPost(array $data, string $field, string $expected): void
    {
        Closure::bind(function() use ($data): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody($data);
        }, $this->view, View::class)();

        $this->assertSame(
            $expected,
            $this->view->Form->selectMulti($field, options: [
                'A',
                'B',
                'C',
            ])
        );
    }
}

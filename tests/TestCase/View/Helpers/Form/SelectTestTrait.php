<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use Closure;
use Fyre\View\View;
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
                '<select id="select" name="select" data-test="[1,2]"></select>',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<select id="select" name="select" data-test="&lt;test&gt;"></select>',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<select class="test" id="select" name="select"></select>',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'other'],
                '<select class="test" id="other" name="select"></select>',
            ],
            'attribute order' => [
                ['id' => 'other', 'class' => 'test'],
                '<select class="test" id="other" name="select"></select>',
            ],
            'id' => [
                ['id' => 'other'],
                '<select id="other" name="select"></select>',
            ],
            'id false' => [
                ['id' => false],
                '<select name="select"></select>',
            ],
            'name' => [
                ['name' => 'other'],
                '<select id="select" name="other"></select>',
            ],
            'name false' => [
                ['name' => false],
                '<select id="select"></select>',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function selectFieldNameProvider(): array
    {
        return [
            'flat' => ['select_value', '<select id="select-value" name="select_value"></select>'],
            'dotted' => ['key.select_value', '<select id="key-select-value" name="key[select_value]"></select>'],
            'deeply dotted' => [
                'deep.key.select_value',
                '<select id="deep-key-select-value" name="deep[key][select_value]"></select>',
            ],
        ];
    }

    /**
     * @return array<string, array{array<mixed>, string}>
     */
    public static function selectOptionsProvider(): array
    {
        return [
            'indexed' => [
                [
                    'A',
                    'B',
                ],
                '<select id="select" name="select"><option value="0">A</option><option value="1">B</option></select>',
            ],
            'associative' => [
                [
                    'a' => 'A',
                ],
                '<select id="select" name="select"><option value="a">A</option></select>',
            ],
            'attribute arrays' => [
                [
                    [
                        'value' => 'a',
                        'label' => 'A',
                    ],
                ],
                '<select id="select" name="select"><option value="a">A</option></select>',
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
                '<select id="select" name="select"><optgroup label="test"><option value="0">A</option><option value="1">B</option></optgroup></select>',
            ],
        ];
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function selectSelectedPostProvider(): array
    {
        return [
            'flat' => [
                [
                    'select' => '1',
                ],
                'select',
                '<select id="select" name="select"><option value="0">A</option><option value="1" selected>B</option></select>',
            ],
            'dotted' => [
                [
                    'key' => [
                        'select' => '1',
                    ],
                ],
                'key.select',
                '<select id="key-select" name="key[select]"><option value="0">A</option><option value="1" selected>B</option></select>',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('selectAttributesProvider')]
    public function testSelectAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->select('select', $attributes)
        );
    }

    #[DataProvider('selectFieldNameProvider')]
    public function testSelectFieldName(string $field, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->select($field)
        );
    }

    public function testSelectIdPrefix(): void
    {
        $this->view->Form->open(idPrefix: 'test');

        $this->assertSame(
            '<select id="test-select" name="select"></select>',
            $this->view->Form->select('select')
        );
    }

    /**
     * @param array<mixed> $options
     */
    #[DataProvider('selectOptionsProvider')]
    public function testSelectOptions(array $options, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->select('select', options: $options)
        );
    }

    public function testSelectOptionsAttributesEscape(): void
    {
        $this->assertSame(
            '<select id="select" name="select"><option data-test="&lt;test&gt;" value="a">A</option></select>',
            $this->view->Form->select('select', options: [
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
            '<select id="select" name="select"><option class="test" value="a">A</option></select>',
            $this->view->Form->select('select', options: [
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
            '<select id="select" name="select"><option value="0">&lt;test&gt;</option></select>',
            $this->view->Form->select('select', options: [
                '<test>',
            ])
        );
    }

    public function testSelectSelected(): void
    {
        $this->assertSame(
            '<select id="select" name="select"><option value="0">A</option><option value="1" selected>B</option></select>',
            $this->view->Form->select('select', [
                'value' => 1,
            ], [
                'A',
                'B',
            ])
        );
    }

    public function testSelectSelectedDefault(): void
    {
        $this->assertSame(
            '<select id="select" name="select"><option value="0">A</option><option value="1" selected>B</option></select>',
            $this->view->Form->select('select', [
                'default' => '1',
            ], [
                'A',
                'B',
            ])
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('selectSelectedPostProvider')]
    public function testSelectSelectedPost(array $data, string $field, string $expected): void
    {
        Closure::bind(function() use ($data): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody($data);
        }, $this->view, View::class)();

        $this->assertSame(
            $expected,
            $this->view->Form->select($field, options: [
                'A',
                'B',
            ])
        );
    }

    public function testSelectSelectedWithoutOptions(): void
    {
        $this->assertSame(
            '<select id="select" name="select"><option value="1" selected></option></select>',
            $this->view->Form->select('select', [
                'value' => '1',
            ])
        );
    }
}

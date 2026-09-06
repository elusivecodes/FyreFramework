<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use Closure;
use Fyre\View\View;
use PHPUnit\Framework\Attributes\DataProvider;

trait NumberTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function numberAttributesProvider(): array
    {
        return [
            'array value' => [
                ['data-test' => [1, 2]],
                '<input id="number" name="number" data-test="[1,2]" type="number" placeholder="Number" />',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<input id="number" name="number" data-test="&lt;test&gt;" type="number" placeholder="Number" />',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<input class="test" id="number" name="number" type="number" placeholder="Number" />',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'other'],
                '<input class="test" id="other" name="number" type="number" placeholder="Number" />',
            ],
            'attribute order' => [
                ['id' => 'other', 'class' => 'test'],
                '<input class="test" id="other" name="number" type="number" placeholder="Number" />',
            ],
            'id' => [
                ['id' => 'other'],
                '<input id="other" name="number" type="number" placeholder="Number" />',
            ],
            'id false' => [
                ['id' => false],
                '<input name="number" type="number" placeholder="Number" />',
            ],
            'name' => [
                ['name' => 'other'],
                '<input id="number" name="other" type="number" placeholder="Number" />',
            ],
            'name false' => [
                ['name' => false],
                '<input id="number" type="number" placeholder="Number" />',
            ],
            'placeholder' => [
                ['placeholder' => 'Other'],
                '<input id="number" name="number" type="number" placeholder="Other" />',
            ],
            'placeholder false' => [
                ['placeholder' => false],
                '<input id="number" name="number" type="number" />',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function numberFieldNameProvider(): array
    {
        return [
            'flat' => [
                'number_value',
                '<input id="number-value" name="number_value" type="number" placeholder="Number Value" />',
            ],
            'dotted' => [
                'key.number_value',
                '<input id="key-number-value" name="key[number_value]" type="number" placeholder="Number Value" />',
            ],
            'deeply dotted' => [
                'deep.key.number_value',
                '<input id="deep-key-number-value" name="deep[key][number_value]" type="number" placeholder="Number Value" />',
            ],
        ];
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function numberValuePostProvider(): array
    {
        return [
            'flat' => [
                [
                    'number' => '123',
                ],
                'number',
                '<input id="number" name="number" type="number" value="123" placeholder="Number" />',
            ],
            'dotted' => [
                [
                    'key' => [
                        'number' => '123',
                    ],
                ],
                'key.number',
                '<input id="key-number" name="key[number]" type="number" value="123" placeholder="Number" />',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('numberAttributesProvider')]
    public function testNumberAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->number('number', $attributes)
        );
    }

    #[DataProvider('numberFieldNameProvider')]
    public function testNumberFieldName(string $field, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->number($field)
        );
    }

    public function testNumberIdPrefix(): void
    {
        $this->view->Form->open(idPrefix: 'test');

        $this->assertSame(
            '<input id="test-number" name="number" type="number" placeholder="Number" />',
            $this->view->Form->number('number')
        );
    }

    public function testNumberValueDefault(): void
    {
        $this->assertSame(
            '<input id="number" name="number" type="number" value="123" placeholder="Number" />',
            $this->view->Form->number('number', [
                'default' => '123',
            ])
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('numberValuePostProvider')]
    public function testNumberValuePost(array $data, string $field, string $expected): void
    {
        Closure::bind(function() use ($data): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody($data);
        }, $this->view, View::class)();

        $this->assertSame(
            $expected,
            $this->view->Form->number($field)
        );
    }
}

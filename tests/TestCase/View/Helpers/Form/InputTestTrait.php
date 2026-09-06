<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use Closure;
use Fyre\View\View;
use PHPUnit\Framework\Attributes\DataProvider;

trait InputTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function inputAttributesProvider(): array
    {
        return [
            'array value' => [
                ['data-test' => [1, 2]],
                '<input id="input" name="input" data-test="[1,2]" type="text" placeholder="Input" />',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<input id="input" name="input" data-test="&lt;test&gt;" type="text" placeholder="Input" />',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<input class="test" id="input" name="input" type="text" placeholder="Input" />',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'other'],
                '<input class="test" id="other" name="input" type="text" placeholder="Input" />',
            ],
            'attribute order' => [
                ['id' => 'other', 'class' => 'test'],
                '<input class="test" id="other" name="input" type="text" placeholder="Input" />',
            ],
            'id' => [
                ['id' => 'other'],
                '<input id="other" name="input" type="text" placeholder="Input" />',
            ],
            'id false' => [
                ['id' => false],
                '<input name="input" type="text" placeholder="Input" />',
            ],
            'name' => [
                ['name' => 'other'],
                '<input id="input" name="other" type="text" placeholder="Input" />',
            ],
            'name false' => [
                ['name' => false],
                '<input id="input" type="text" placeholder="Input" />',
            ],
            'placeholder' => [
                ['placeholder' => 'Other'],
                '<input id="input" name="input" type="text" placeholder="Other" />',
            ],
            'placeholder false' => [
                ['placeholder' => false],
                '<input id="input" name="input" type="text" />',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function inputFieldNameProvider(): array
    {
        return [
            'flat' => ['input_value', '<input id="input-value" name="input_value" type="text" placeholder="Input Value" />'],
            'dotted' => [
                'key.input_value',
                '<input id="key-input-value" name="key[input_value]" type="text" placeholder="Input Value" />',
            ],
            'deeply dotted' => [
                'deep.key.input_value',
                '<input id="deep-key-input-value" name="deep[key][input_value]" type="text" placeholder="Input Value" />',
            ],
        ];
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function inputValuePostProvider(): array
    {
        return [
            'flat' => [
                [
                    'input' => 'test',
                ],
                'input',
                '<input id="input" name="input" type="text" value="test" placeholder="Input" />',
            ],
            'dotted' => [
                [
                    'key' => [
                        'input' => 'test',
                    ],
                ],
                'key.input',
                '<input id="key-input" name="key[input]" type="text" value="test" placeholder="Input" />',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('inputAttributesProvider')]
    public function testInputAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->input('input', $attributes)
        );
    }

    #[DataProvider('inputFieldNameProvider')]
    public function testInputFieldName(string $field, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->input($field)
        );
    }

    public function testInputIdPrefix(): void
    {
        $this->view->Form->open(idPrefix: 'test');

        $this->assertSame(
            '<input id="test-input" name="input" type="text" placeholder="Input" />',
            $this->view->Form->input('input')
        );
    }

    public function testInputValueDefault(): void
    {
        $this->assertSame(
            '<input id="input" name="input" type="text" value="test" placeholder="Input" />',
            $this->view->Form->input('input', [
                'default' => 'test',
            ])
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('inputValuePostProvider')]
    public function testInputValuePost(array $data, string $field, string $expected): void
    {
        Closure::bind(function() use ($data): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody($data);
        }, $this->view, View::class)();

        $this->assertSame(
            $expected,
            $this->view->Form->input($field)
        );
    }

    public function testInputValuePostNull(): void
    {
        Closure::bind(function(): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody([
                'input' => null,
            ]);
        }, $this->view, View::class)();

        $this->assertSame(
            '<input id="input" name="input" type="text" placeholder="Input" />',
            $this->view->Form->input('input', [
                'default' => 'test',
            ])
        );
    }
}

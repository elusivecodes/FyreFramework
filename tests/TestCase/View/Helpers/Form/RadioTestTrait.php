<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use Closure;
use Fyre\View\View;
use PHPUnit\Framework\Attributes\DataProvider;

trait RadioTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function radioAttributesProvider(): array
    {
        return [
            'array value' => [
                ['value' => '1', 'data-test' => [1, 2]],
                '<input id="radio" name="radio" data-test="[1,2]" type="radio" value="1" />',
            ],
            'escaped value' => [
                ['value' => '1', 'data-test' => '<test>'],
                '<input id="radio" name="radio" data-test="&lt;test&gt;" type="radio" value="1" />',
            ],
            'invalid name' => [
                ['value' => '1', '*class*' => 'test'],
                '<input class="test" id="radio" name="radio" type="radio" value="1" />',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'other', 'value' => '1'],
                '<input class="test" id="other" name="radio" type="radio" value="1" />',
            ],
            'attribute order' => [
                ['id' => 'other', 'class' => 'test', 'value' => '1'],
                '<input class="test" id="other" name="radio" type="radio" value="1" />',
            ],
            'id' => [
                ['id' => 'other', 'value' => '1'],
                '<input id="other" name="radio" type="radio" value="1" />',
            ],
            'id false' => [
                ['id' => false, 'value' => '1'],
                '<input name="radio" type="radio" value="1" />',
            ],
            'name' => [
                ['name' => 'other', 'value' => '1'],
                '<input id="radio" name="other" type="radio" value="1" />',
            ],
            'name false' => [
                ['name' => false, 'value' => '1'],
                '<input id="radio" type="radio" value="1" />',
            ],
        ];
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function radioCheckedPostProvider(): array
    {
        return [
            'flat' => [
                [
                    'radio' => '1',
                ],
                'radio',
                '<input id="radio" name="radio" type="radio" value="1" checked />',
            ],
            'dotted' => [
                [
                    'key' => [
                        'radio' => '1',
                    ],
                ],
                'key.radio',
                '<input id="key-radio" name="key[radio]" type="radio" value="1" checked />',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function radioFieldNameProvider(): array
    {
        return [
            'flat' => ['radio_value', '<input id="radio-value" name="radio_value" type="radio" value="1" />'],
            'dotted' => ['key.radio_value', '<input id="key-radio-value" name="key[radio_value]" type="radio" value="1" />'],
            'deeply dotted' => [
                'deep.key.radio_value',
                '<input id="deep-key-radio-value" name="deep[key][radio_value]" type="radio" value="1" />',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('radioAttributesProvider')]
    public function testRadioAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->radio('radio', $attributes)
        );
    }

    public function testRadioChecked(): void
    {
        $this->assertSame(
            '<input id="radio" name="radio" type="radio" value="1" checked />',
            $this->view->Form->radio('radio', [
                'value' => '1',
                'checked' => true,
            ])
        );
    }

    public function testRadioCheckedDefault(): void
    {
        $this->assertSame(
            '<input id="radio" name="radio" type="radio" value="1" checked />',
            $this->view->Form->radio('radio', [
                'value' => '1',
                'default' => '1',
            ])
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('radioCheckedPostProvider')]
    public function testRadioCheckedPost(array $data, string $field, string $expected): void
    {
        Closure::bind(function() use ($data): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody($data);
        }, $this->view, View::class)();

        $this->assertSame(
            $expected,
            $this->view->Form->radio($field, [
                'value' => '1',
            ])
        );
    }

    #[DataProvider('radioFieldNameProvider')]
    public function testRadioFieldName(string $field, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->radio($field, [
                'value' => '1',
            ])
        );
    }

    public function testRadioIdPrefix(): void
    {
        $this->view->Form->open(idPrefix: 'test');

        $this->assertSame(
            '<input id="test-radio" name="radio" type="radio" value="1" />',
            $this->view->Form->radio('radio', [
                'value' => '1',
            ])
        );
    }

    public function testRadioValue(): void
    {
        $this->assertSame(
            '<input id="radio" name="radio" type="radio" value="on" />',
            $this->view->Form->radio('radio', [
                'value' => 'on',
            ])
        );
    }
}

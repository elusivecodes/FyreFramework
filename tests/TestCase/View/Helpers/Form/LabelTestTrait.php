<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use PHPUnit\Framework\Attributes\DataProvider;

trait LabelTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function labelAttributesProvider(): array
    {
        return [
            'array value' => [
                ['data-test' => [1, 2]],
                '<label data-test="[1,2]" for="input">Input</label>',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<label data-test="&lt;test&gt;" for="input">Input</label>',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<label class="test" for="input">Input</label>',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'label'],
                '<label class="test" id="label" for="input">Input</label>',
            ],
            'attribute order' => [
                ['id' => 'label', 'class' => 'test'],
                '<label class="test" id="label" for="input">Input</label>',
            ],
            'for' => [
                ['for' => 'other'],
                '<label for="other">Input</label>',
            ],
            'for false' => [
                ['for' => false],
                '<label>Input</label>',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function labelTextProvider(): array
    {
        return [
            'plain text' => ['Test', '<label for="input">Test</label>'],
            'empty text' => ['', '<label for="input"></label>'],
            'escaped text' => ['<i>Test</i>', '<label for="input">&lt;i&gt;Test&lt;/i&gt;</label>'],
        ];
    }

    public function testLabel(): void
    {
        $this->assertSame(
            '<label for="input-value">Input Value</label>',
            $this->view->Form->label('input_value')
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('labelAttributesProvider')]
    public function testLabelAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->label('input', $attributes)
        );
    }

    public function testLabelDot(): void
    {
        $this->assertSame(
            '<label for="key-input-value">Input Value</label>',
            $this->view->Form->label('key.input_value')
        );
    }

    #[DataProvider('labelTextProvider')]
    public function testLabelText(string $text, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->label('input', text: $text)
        );
    }

    public function testLabelTextNoEscape(): void
    {
        $this->assertSame(
            '<label for="input"><i>Test</i></label>',
            $this->view->Form->label('input', text: '<i>Test</i>', escape: false)
        );
    }
}

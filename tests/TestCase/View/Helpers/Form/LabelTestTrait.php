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

    public function testLabelFor(): void
    {
        $this->assertSame(
            '<label for="other">Input</label>',
            $this->view->Form->label('input', [
                'for' => 'other',
            ])
        );
    }

    public function testLabelForFalse(): void
    {
        $this->assertSame(
            '<label>Input</label>',
            $this->view->Form->label('input', [
                'for' => false,
            ])
        );
    }

    public function testLabelText(): void
    {
        $this->assertSame(
            '<label for="input">Test</label>',
            $this->view->Form->label('input', text: 'Test')
        );
    }

    public function testLabelTextEmpty(): void
    {
        $this->assertSame(
            '<label for="input"></label>',
            $this->view->Form->label('input', text: '')
        );
    }

    public function testLabelTextEscape(): void
    {
        $this->assertSame(
            '<label for="input">&lt;i&gt;Test&lt;/i&gt;</label>',
            $this->view->Form->label('input', text: '<i>Test</i>')
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

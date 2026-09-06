<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FormBuilder;

use PHPUnit\Framework\Attributes\DataProvider;

trait TextareaTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function textareaAttributesProvider(): array
    {
        return [
            'array value' => [
                ['data-test' => [1, 2]],
                '<textarea data-test="[1,2]"></textarea>',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<textarea data-test="&lt;test&gt;"></textarea>',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<textarea class="test"></textarea>',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'textarea'],
                '<textarea class="test" id="textarea"></textarea>',
            ],
            'attribute order' => [
                ['id' => 'textarea', 'class' => 'test'],
                '<textarea class="test" id="textarea"></textarea>',
            ],
        ];
    }

    public function testTextarea(): void
    {
        $this->assertSame(
            '<textarea></textarea>',
            $this->form->textarea()
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('textareaAttributesProvider')]
    public function testTextareaAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->form->textarea(attributes: $attributes)
        );
    }

    public function testTextareaName(): void
    {
        $this->assertSame(
            '<textarea name="textarea"></textarea>',
            $this->form->textarea('textarea')
        );
    }

    public function testTextareaValue(): void
    {
        $this->assertSame(
            '<textarea>Test</textarea>',
            $this->form->textarea(attributes: [
                'value' => 'Test',
            ])
        );
    }

    public function testTextareaValueEscape(): void
    {
        $this->assertSame(
            '<textarea>&lt;test&gt;</textarea>',
            $this->form->textarea(attributes: [
                'value' => '<test>',
            ])
        );
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FormBuilder;

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
                '<label data-test="[1,2]"></label>',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<label data-test="&lt;test&gt;"></label>',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<label class="test"></label>',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'label'],
                '<label class="test" id="label"></label>',
            ],
            'attribute order' => [
                ['id' => 'label', 'class' => 'test'],
                '<label class="test" id="label"></label>',
            ],
        ];
    }

    public function testLabel(): void
    {
        $this->assertSame(
            '<label></label>',
            $this->form->label()
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
            $this->form->label('', $attributes)
        );
    }

    public function testLabelContent(): void
    {
        $this->assertSame(
            '<label>Test</label>',
            $this->form->label('Test')
        );
    }

    public function testLabelContentEscape(): void
    {
        $this->assertSame(
            '<label>&lt;i&gt;Test&lt;/i&gt;</label>',
            $this->form->label('<i>Test</i>')
        );
    }

    public function testLabelContentNoEscape(): void
    {
        $this->assertSame(
            '<label><i>Test</i></label>',
            $this->form->label('<i>Test</i>', escape: false)
        );
    }
}

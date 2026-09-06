<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FormBuilder;

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
                '<input data-test="[1,2]" type="text" />',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<input data-test="&lt;test&gt;" type="text" />',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<input class="test" type="text" />',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'input'],
                '<input class="test" id="input" type="text" />',
            ],
            'attribute order' => [
                ['id' => 'input', 'class' => 'test'],
                '<input class="test" id="input" type="text" />',
            ],
        ];
    }

    public function testInput(): void
    {
        $this->assertSame(
            '<input type="text" />',
            $this->form->input()
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('inputAttributesProvider')]
    public function testInputAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->form->input(attributes: $attributes)
        );
    }

    public function testInputName(): void
    {
        $this->assertSame(
            '<input name="input" type="text" />',
            $this->form->input('input')
        );
    }
}

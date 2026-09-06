<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use PHPUnit\Framework\Attributes\DataProvider;

trait LegendTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function legendAttributesProvider(): array
    {
        return [
            'array value' => [
                ['data-test' => [1, 2]],
                '<legend data-test="[1,2]"></legend>',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<legend data-test="&lt;test&gt;"></legend>',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<legend class="test"></legend>',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'legend'],
                '<legend class="test" id="legend"></legend>',
            ],
            'attribute order' => [
                ['id' => 'legend', 'class' => 'test'],
                '<legend class="test" id="legend"></legend>',
            ],
        ];
    }

    public function testLegend(): void
    {
        $this->assertSame(
            '<legend></legend>',
            $this->view->Form->legend()
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('legendAttributesProvider')]
    public function testLegendAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->legend('', $attributes)
        );
    }

    public function testLegendContent(): void
    {
        $this->assertSame(
            '<legend>Test</legend>',
            $this->view->Form->legend('Test')
        );
    }

    public function testLegendContentEscape(): void
    {
        $this->assertSame(
            '<legend>&lt;i&gt;Test&lt;/i&gt;</legend>',
            $this->view->Form->legend('<i>Test</i>')
        );
    }

    public function testLegendContentNoEscape(): void
    {
        $this->assertSame(
            '<legend><i>Test</i></legend>',
            $this->view->Form->legend('<i>Test</i>', escape: false)
        );
    }
}

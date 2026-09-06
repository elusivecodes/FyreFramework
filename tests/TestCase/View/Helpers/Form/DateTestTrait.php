<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use Closure;
use Fyre\Utility\DateTime\Date;
use Fyre\View\View;
use PHPUnit\Framework\Attributes\DataProvider;

trait DateTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function dateAttributesProvider(): array
    {
        return [
            'array value' => [
                ['data-test' => [1, 2]],
                '<input id="date" name="date" data-test="[1,2]" type="date" />',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<input id="date" name="date" data-test="&lt;test&gt;" type="date" />',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<input class="test" id="date" name="date" type="date" />',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'other'],
                '<input class="test" id="other" name="date" type="date" />',
            ],
            'attribute order' => [
                ['id' => 'other', 'class' => 'test'],
                '<input class="test" id="other" name="date" type="date" />',
            ],
        ];
    }

    public function testDate(): void
    {
        $this->assertSame(
            '<input id="date-value" name="date_value" type="date" />',
            $this->view->Form->date('date_value')
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('dateAttributesProvider')]
    public function testDateAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->date('date', $attributes)
        );
    }

    public function testDateDot(): void
    {
        $this->assertSame(
            '<input id="key-date-value" name="key[date_value]" type="date" />',
            $this->view->Form->date('key.date_value')
        );
    }

    public function testDateDotDeep(): void
    {
        $this->assertSame(
            '<input id="deep-key-date-value" name="deep[key][date_value]" type="date" />',
            $this->view->Form->date('deep.key.date_value')
        );
    }

    public function testDateId(): void
    {
        $this->assertSame(
            '<input id="other" name="date" type="date" />',
            $this->view->Form->date('date', [
                'id' => 'other',
            ])
        );
    }

    public function testDateIdFalse(): void
    {
        $this->assertSame(
            '<input name="date" type="date" />',
            $this->view->Form->date('date', [
                'id' => false,
            ])
        );
    }

    public function testDateIdPrefix(): void
    {
        $this->view->Form->open(idPrefix: 'test');

        $this->assertSame(
            '<input id="test-date" name="date" type="date" />',
            $this->view->Form->date('date')
        );
    }

    public function testDateName(): void
    {
        $this->assertSame(
            '<input id="date" name="other" type="date" />',
            $this->view->Form->date('date', [
                'name' => 'other',
            ])
        );
    }

    public function testDateNameFalse(): void
    {
        $this->assertSame(
            '<input id="date" type="date" />',
            $this->view->Form->date('date', [
                'name' => false,
            ])
        );
    }

    public function testDateValueDefault(): void
    {
        $date = Date::createFromArray([2022, 1, 1]);

        $this->assertSame(
            '<input id="date" name="date" type="date" value="2022-01-01" />',
            $this->view->Form->date('date', [
                'default' => $date,
            ])
        );
    }

    public function testDateValuePost(): void
    {
        Closure::bind(function(): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody([
                'date' => '2022-01-01',
            ]);
        }, $this->view, View::class)();

        $this->assertSame(
            '<input id="date" name="date" type="date" value="2022-01-01" />',
            $this->view->Form->date('date')
        );
    }

    public function testDateValuePostDot(): void
    {
        Closure::bind(function(): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody([
                'key' => [
                    'date' => '2022-01-01',
                ],
            ]);
        }, $this->view, View::class)();

        $this->assertSame(
            '<input id="key-date" name="key[date]" type="date" value="2022-01-01" />',
            $this->view->Form->date('key.date')
        );
    }
}

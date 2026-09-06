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
            'id' => [
                ['id' => 'other'],
                '<input id="other" name="date" type="date" />',
            ],
            'id false' => [
                ['id' => false],
                '<input name="date" type="date" />',
            ],
            'name' => [
                ['name' => 'other'],
                '<input id="date" name="other" type="date" />',
            ],
            'name false' => [
                ['name' => false],
                '<input id="date" type="date" />',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function dateFieldNameProvider(): array
    {
        return [
            'flat' => ['date_value', '<input id="date-value" name="date_value" type="date" />'],
            'dotted' => ['key.date_value', '<input id="key-date-value" name="key[date_value]" type="date" />'],
            'deeply dotted' => ['deep.key.date_value', '<input id="deep-key-date-value" name="deep[key][date_value]" type="date" />'],
        ];
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function dateValuePostProvider(): array
    {
        return [
            'flat' => [
                [
                    'date' => '2022-01-01',
                ],
                'date',
                '<input id="date" name="date" type="date" value="2022-01-01" />',
            ],
            'dotted' => [
                [
                    'key' => [
                        'date' => '2022-01-01',
                    ],
                ],
                'key.date',
                '<input id="key-date" name="key[date]" type="date" value="2022-01-01" />',
            ],
        ];
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

    #[DataProvider('dateFieldNameProvider')]
    public function testDateFieldName(string $field, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->date($field)
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

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('dateValuePostProvider')]
    public function testDateValuePost(array $data, string $field, string $expected): void
    {
        Closure::bind(function() use ($data): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody($data);
        }, $this->view, View::class)();

        $this->assertSame(
            $expected,
            $this->view->Form->date($field)
        );
    }
}

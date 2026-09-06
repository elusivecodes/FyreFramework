<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use Closure;
use Fyre\Utility\DateTime\DateTime;
use Fyre\View\View;
use PHPUnit\Framework\Attributes\DataProvider;

trait DateTimeTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function dateTimeAttributesProvider(): array
    {
        return [
            'array value' => [
                ['data-test' => [1, 2]],
                '<input id="datetime" name="datetime" data-test="[1,2]" type="datetime-local" />',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<input id="datetime" name="datetime" data-test="&lt;test&gt;" type="datetime-local" />',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<input class="test" id="datetime" name="datetime" type="datetime-local" />',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'other'],
                '<input class="test" id="other" name="datetime" type="datetime-local" />',
            ],
            'attribute order' => [
                ['id' => 'other', 'class' => 'test'],
                '<input class="test" id="other" name="datetime" type="datetime-local" />',
            ],
            'id' => [
                ['id' => 'other'],
                '<input id="other" name="datetime" type="datetime-local" />',
            ],
            'id false' => [
                ['id' => false],
                '<input name="datetime" type="datetime-local" />',
            ],
            'name' => [
                ['name' => 'other'],
                '<input id="datetime" name="other" type="datetime-local" />',
            ],
            'name false' => [
                ['name' => false],
                '<input id="datetime" type="datetime-local" />',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function dateTimeFieldNameProvider(): array
    {
        return [
            'flat' => ['datetime_value', '<input id="datetime-value" name="datetime_value" type="datetime-local" />'],
            'dotted' => [
                'key.datetime_value',
                '<input id="key-datetime-value" name="key[datetime_value]" type="datetime-local" />',
            ],
            'deeply dotted' => [
                'deep.key.datetime_value',
                '<input id="deep-key-datetime-value" name="deep[key][datetime_value]" type="datetime-local" />',
            ],
        ];
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function dateTimeValuePostProvider(): array
    {
        return [
            'flat' => [
                [
                    'datetime' => '2022-01-01T00:00',
                ],
                'datetime',
                '<input id="datetime" name="datetime" type="datetime-local" value="2022-01-01T00:00" />',
            ],
            'dotted' => [
                [
                    'key' => [
                        'datetime' => '2022-01-01T00:00',
                    ],
                ],
                'key.datetime',
                '<input id="key-datetime" name="key[datetime]" type="datetime-local" value="2022-01-01T00:00" />',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('dateTimeAttributesProvider')]
    public function testDateTimeAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->datetime('datetime', $attributes)
        );
    }

    #[DataProvider('dateTimeFieldNameProvider')]
    public function testDateTimeFieldName(string $field, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->datetime($field)
        );
    }

    public function testDateTimeIdPrefix(): void
    {
        $this->view->Form->open(idPrefix: 'test');

        $this->assertSame(
            '<input id="test-datetime" name="datetime" type="datetime-local" />',
            $this->view->Form->datetime('datetime')
        );
    }

    public function testDateTimeValueDefault(): void
    {
        $now = DateTime::createFromArray([2022, 1, 1]);

        $this->assertSame(
            '<input id="datetime" name="datetime" type="datetime-local" value="2022-01-01T00:00" />',
            $this->view->Form->datetime('datetime', [
                'default' => $now,
            ])
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('dateTimeValuePostProvider')]
    public function testDateTimeValuePost(array $data, string $field, string $expected): void
    {
        Closure::bind(function() use ($data): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody($data);
        }, $this->view, View::class)();

        $this->assertSame(
            $expected,
            $this->view->Form->datetime($field)
        );
    }
}

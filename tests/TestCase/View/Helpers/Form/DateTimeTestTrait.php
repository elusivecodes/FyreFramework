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
        ];
    }

    public function testDateTime(): void
    {
        $this->assertSame(
            '<input id="datetime-value" name="datetime_value" type="datetime-local" />',
            $this->view->Form->datetime('datetime_value')
        );
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

    public function testDateTimeDot(): void
    {
        $this->assertSame(
            '<input id="key-datetime-value" name="key[datetime_value]" type="datetime-local" />',
            $this->view->Form->datetime('key.datetime_value')
        );
    }

    public function testDateTimeDotDeep(): void
    {
        $this->assertSame(
            '<input id="deep-key-datetime-value" name="deep[key][datetime_value]" type="datetime-local" />',
            $this->view->Form->datetime('deep.key.datetime_value')
        );
    }

    public function testDateTimeId(): void
    {
        $this->assertSame(
            '<input id="other" name="datetime" type="datetime-local" />',
            $this->view->Form->datetime('datetime', [
                'id' => 'other',
            ])
        );
    }

    public function testDateTimeIdFalse(): void
    {
        $this->assertSame(
            '<input name="datetime" type="datetime-local" />',
            $this->view->Form->datetime('datetime', [
                'id' => false,
            ])
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

    public function testDateTimeName(): void
    {
        $this->assertSame(
            '<input id="datetime" name="other" type="datetime-local" />',
            $this->view->Form->datetime('datetime', [
                'name' => 'other',
            ])
        );
    }

    public function testDateTimeNameFalse(): void
    {
        $this->assertSame(
            '<input id="datetime" type="datetime-local" />',
            $this->view->Form->datetime('datetime', [
                'name' => false,
            ])
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

    public function testDateTimeValuePost(): void
    {
        Closure::bind(function(): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody([
                'datetime' => '2022-01-01T00:00',
            ]);
        }, $this->view, View::class)();

        $this->assertSame(
            '<input id="datetime" name="datetime" type="datetime-local" value="2022-01-01T00:00" />',
            $this->view->Form->datetime('datetime')
        );
    }

    public function testDateTimeValuePostDot(): void
    {
        Closure::bind(function(): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody([
                'key' => [
                    'datetime' => '2022-01-01T00:00',
                ],
            ]);
        }, $this->view, View::class)();

        $this->assertSame(
            '<input id="key-datetime" name="key[datetime]" type="datetime-local" value="2022-01-01T00:00" />',
            $this->view->Form->datetime('key.datetime')
        );
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use Closure;
use Fyre\Utility\DateTime\Time;
use Fyre\View\View;
use PHPUnit\Framework\Attributes\DataProvider;

trait TimeTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function timeAttributesProvider(): array
    {
        return [
            'array value' => [
                ['data-test' => [1, 2]],
                '<input id="time" name="time" data-test="[1,2]" type="time" />',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<input id="time" name="time" data-test="&lt;test&gt;" type="time" />',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<input class="test" id="time" name="time" type="time" />',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'other'],
                '<input class="test" id="other" name="time" type="time" />',
            ],
            'attribute order' => [
                ['id' => 'other', 'class' => 'test'],
                '<input class="test" id="other" name="time" type="time" />',
            ],
            'id' => [
                ['id' => 'other'],
                '<input id="other" name="time" type="time" />',
            ],
            'id false' => [
                ['id' => false],
                '<input name="time" type="time" />',
            ],
            'name' => [
                ['name' => 'other'],
                '<input id="time" name="other" type="time" />',
            ],
            'name false' => [
                ['name' => false],
                '<input id="time" type="time" />',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function timeFieldNameProvider(): array
    {
        return [
            'flat' => ['time_value', '<input id="time-value" name="time_value" type="time" />'],
            'dotted' => ['key.time_value', '<input id="key-time-value" name="key[time_value]" type="time" />'],
            'deeply dotted' => ['deep.key.time_value', '<input id="deep-key-time-value" name="deep[key][time_value]" type="time" />'],
        ];
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function timeValuePostProvider(): array
    {
        return [
            'flat' => [
                [
                    'time' => '00:00',
                ],
                'time',
                '<input id="time" name="time" type="time" value="00:00" />',
            ],
            'dotted' => [
                [
                    'key' => [
                        'time' => '00:00',
                    ],
                ],
                'key.time',
                '<input id="key-time" name="key[time]" type="time" value="00:00" />',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('timeAttributesProvider')]
    public function testTimeAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->time('time', $attributes)
        );
    }

    #[DataProvider('timeFieldNameProvider')]
    public function testTimeFieldName(string $field, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->time($field)
        );
    }

    public function testTimeIdPrefix(): void
    {
        $this->view->Form->open(idPrefix: 'test');

        $this->assertSame(
            '<input id="test-time" name="time" type="time" />',
            $this->view->Form->time('time')
        );
    }

    public function testTimeValueDefault(): void
    {
        $time = Time::createFromArray([0, 0]);

        $this->assertSame(
            '<input id="time" name="time" type="time" value="00:00" />',
            $this->view->Form->time('time', [
                'default' => $time,
            ])
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('timeValuePostProvider')]
    public function testTimeValuePost(array $data, string $field, string $expected): void
    {
        Closure::bind(function() use ($data): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody($data);
        }, $this->view, View::class)();

        $this->assertSame(
            $expected,
            $this->view->Form->time($field)
        );
    }
}

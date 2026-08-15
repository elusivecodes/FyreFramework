<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FormBuilder;

use BadMethodCallException;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @phpstan-type InputMethod 'checkbox'|'color'|'date'|'datetime'|'email'|'file'|'hidden'|'image'|'month'|'number'|'password'|'radio'|'range'|'reset'|'search'|'submit'|'tel'|'text'|'time'|'url'|'week'
 */
trait InputTypeTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function inputTypeProvider(): array
    {
        return [
            'checkbox' => ['checkbox', 'checkbox'],
            'color' => ['color', 'color'],
            'date' => ['date', 'date'],
            'date time local' => ['datetime', 'datetime-local'],
            'email' => ['email', 'email'],
            'file' => ['file', 'file'],
            'hidden' => ['hidden', 'hidden'],
            'image' => ['image', 'image'],
            'month' => ['month', 'month'],
            'number' => ['number', 'number'],
            'password' => ['password', 'password'],
            'radio' => ['radio', 'radio'],
            'range' => ['range', 'range'],
            'reset' => ['reset', 'reset'],
            'search' => ['search', 'search'],
            'submit' => ['submit', 'submit'],
            'telephone' => ['tel', 'tel'],
            'text' => ['text', 'text'],
            'time' => ['time', 'time'],
            'url' => ['url', 'url'],
            'week' => ['week', 'week'],
        ];
    }

    /**
     * @param InputMethod $method
     */
    #[DataProvider('inputTypeProvider')]
    public function testInputType(string $method, string $type): void
    {
        $this->assertSame(
            '<input type="'.$type.'" />',
            $this->form->$method()
        );
    }

    public function testInputTypeAttributes(): void
    {
        $this->assertSame(
            '<input class="test" id="number" name="number" type="number" />',
            $this->form->number('number', [
                'class' => 'test',
                'id' => 'number',
            ])
        );
    }

    public function testInputTypeInvalid(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Input type `invalid` is not valid.');

        $this->form->__call('invalid', []);
    }

    public function testInputTypeName(): void
    {
        $this->assertSame(
            '<input name="number" type="number" />',
            $this->form->number('number')
        );
    }
}

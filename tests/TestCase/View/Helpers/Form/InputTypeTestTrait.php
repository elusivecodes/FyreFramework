<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use PHPUnit\Framework\Attributes\DataProvider;

trait InputTypeTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function inputTypeShortcutProvider(): array
    {
        return [
            'color' => ['color', '<input id="color" name="color" type="color" />'],
            'email' => ['email', '<input id="email" name="email" type="email" placeholder="Email" />'],
            'file' => ['file', '<input id="file" name="file" type="file" />'],
            'hidden' => ['hidden', '<input id="hidden" name="hidden" type="hidden" />'],
            'image' => ['image', '<input id="image" name="image" type="image" />'],
            'month' => ['month', '<input id="month" name="month" type="month" />'],
            'password' => ['password', '<input id="password" name="password" type="password" placeholder="Password" />'],
            'range' => ['range', '<input id="range" name="range" type="range" />'],
            'reset' => ['reset', '<input id="reset" name="reset" type="reset" />'],
            'search' => ['search', '<input id="search" name="search" type="search" placeholder="Search" />'],
            'submit' => ['submit', '<input id="submit" name="submit" type="submit" />'],
            'tel' => ['tel', '<input id="tel" name="tel" type="tel" placeholder="Tel" />'],
            'text' => ['text', '<input id="text" name="text" type="text" placeholder="Text" />'],
            'url' => ['url', '<input id="url" name="url" type="url" placeholder="Url" />'],
            'week' => ['week', '<input id="week" name="week" type="week" />'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function inputTypeSuppressesValueProvider(): array
    {
        return [
            'file' => ['file', '<input id="file" name="file" type="file" />'],
            'image' => ['image', '<input id="image" name="image" type="image" />'],
            'password' => ['password', '<input id="password" name="password" type="password" placeholder="Password" />'],
        ];
    }

    public function testInputTypeAttributes(): void
    {
        $this->assertSame(
            '<input class="test" id="number" name="number" type="number" placeholder="Number" />',
            $this->view->Form->number('number', [
                'class' => 'test',
                'id' => 'number',
            ])
        );
    }

    public function testInputTypeName(): void
    {
        $this->assertSame(
            '<input id="number" name="number" type="number" placeholder="Number" />',
            $this->view->Form->number('number')
        );
    }

    #[DataProvider('inputTypeShortcutProvider')]
    public function testInputTypeShortcut(string $type, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->$type($type)
        );
    }

    #[DataProvider('inputTypeSuppressesValueProvider')]
    public function testInputTypeSuppressesValue(string $type, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->$type($type, [
                'value' => 'test',
            ])
        );
    }
}

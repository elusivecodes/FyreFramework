<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use Closure;
use Fyre\View\View;
use PHPUnit\Framework\Attributes\DataProvider;

trait CheckboxTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function checkboxAttributesProvider(): array
    {
        return [
            'array value' => [
                ['data-test' => [1, 2]],
                '<input name="checkbox" type="hidden" value="0" /><input id="checkbox" name="checkbox" data-test="[1,2]" type="checkbox" value="1" />',
            ],
            'escaped value' => [
                ['data-test' => '<test>'],
                '<input name="checkbox" type="hidden" value="0" /><input id="checkbox" name="checkbox" data-test="&lt;test&gt;" type="checkbox" value="1" />',
            ],
            'invalid name' => [
                ['*class*' => 'test'],
                '<input name="checkbox" type="hidden" value="0" /><input class="test" id="checkbox" name="checkbox" type="checkbox" value="1" />',
            ],
            'multiple attributes' => [
                ['class' => 'test', 'id' => 'other'],
                '<input name="checkbox" type="hidden" value="0" /><input class="test" id="other" name="checkbox" type="checkbox" value="1" />',
            ],
            'attribute order' => [
                ['id' => 'other', 'class' => 'test'],
                '<input name="checkbox" type="hidden" value="0" /><input class="test" id="other" name="checkbox" type="checkbox" value="1" />',
            ],
        ];
    }

    public function testCheckbox(): void
    {
        $this->assertSame(
            '<input name="checkbox_value" type="hidden" value="0" /><input id="checkbox-value" name="checkbox_value" type="checkbox" value="1" />',
            $this->view->Form->checkbox('checkbox_value')
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('checkboxAttributesProvider')]
    public function testCheckboxAttributes(array $attributes, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->view->Form->checkbox('checkbox', $attributes)
        );
    }

    public function testCheckboxChecked(): void
    {
        $this->assertSame(
            '<input name="checkbox" type="hidden" value="0" /><input id="checkbox" name="checkbox" type="checkbox" value="1" checked />',
            $this->view->Form->checkbox('checkbox', [
                'checked' => true,
            ])
        );
    }

    public function testCheckboxCheckedDefault(): void
    {
        $this->assertSame(
            '<input name="checkbox" type="hidden" value="0" /><input id="checkbox" name="checkbox" type="checkbox" value="1" checked />',
            $this->view->Form->checkbox('checkbox', [
                'default' => '1',
            ])
        );
    }

    public function testCheckboxCheckedPost(): void
    {
        Closure::bind(function(): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody([
                'checkbox' => '1',
            ]);
        }, $this->view, View::class)();

        $this->assertSame(
            '<input name="checkbox" type="hidden" value="0" /><input id="checkbox" name="checkbox" type="checkbox" value="1" checked />',
            $this->view->Form->checkbox('checkbox')
        );
    }

    public function testCheckboxCheckedPostDot(): void
    {
        Closure::bind(function(): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody([
                'key' => [
                    'checkbox' => '1',
                ],
            ]);
        }, $this->view, View::class)();

        $this->assertSame(
            '<input name="key[checkbox]" type="hidden" value="0" /><input id="key-checkbox" name="key[checkbox]" type="checkbox" value="1" checked />',
            $this->view->Form->checkbox('key.checkbox')
        );
    }

    public function testCheckboxDot(): void
    {
        $this->assertSame(
            '<input name="key[checkbox_value]" type="hidden" value="0" /><input id="key-checkbox-value" name="key[checkbox_value]" type="checkbox" value="1" />',
            $this->view->Form->checkbox('key.checkbox_value')
        );
    }

    public function testCheckboxDotDeep(): void
    {
        $this->assertSame(
            '<input name="deep[key][checkbox_value]" type="hidden" value="0" /><input id="deep-key-checkbox-value" name="deep[key][checkbox_value]" type="checkbox" value="1" />',
            $this->view->Form->checkbox('deep.key.checkbox_value')
        );
    }

    public function testCheckboxHiddenFieldFalse(): void
    {
        $this->assertSame(
            '<input id="checkbox" name="checkbox" type="checkbox" value="1" />',
            $this->view->Form->checkbox('checkbox', hiddenField: false)
        );
    }

    public function testCheckboxId(): void
    {
        $this->assertSame(
            '<input name="checkbox" type="hidden" value="0" /><input id="other" name="checkbox" type="checkbox" value="1" />',
            $this->view->Form->checkbox('checkbox', [
                'id' => 'other',
            ])
        );
    }

    public function testCheckboxIdFalse(): void
    {
        $this->assertSame(
            '<input name="checkbox" type="hidden" value="0" /><input name="checkbox" type="checkbox" value="1" />',
            $this->view->Form->checkbox('checkbox', [
                'id' => false,
            ])
        );
    }

    public function testCheckboxIdPrefix(): void
    {
        $this->view->Form->open(idPrefix: 'test');

        $this->assertSame(
            '<input name="checkbox" type="hidden" value="0" /><input id="test-checkbox" name="checkbox" type="checkbox" value="1" />',
            $this->view->Form->checkbox('checkbox')
        );
    }

    public function testCheckboxName(): void
    {
        $this->assertSame(
            '<input name="other" type="hidden" value="0" /><input id="checkbox" name="other" type="checkbox" value="1" />',
            $this->view->Form->checkbox('checkbox', [
                'name' => 'other',
            ])
        );
    }

    public function testCheckboxNameFalse(): void
    {
        $this->assertSame(
            '<input id="checkbox" type="checkbox" value="1" />',
            $this->view->Form->checkbox('checkbox', [
                'name' => false,
            ])
        );
    }

    public function testCheckboxValue(): void
    {
        $this->assertSame(
            '<input name="checkbox" type="hidden" value="0" /><input id="checkbox" name="checkbox" type="checkbox" value="on" />',
            $this->view->Form->checkbox('checkbox', [
                'value' => 'on',
            ])
        );
    }
}

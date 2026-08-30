<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use Closure;
use Fyre\View\View;

trait InputTestTrait
{
    public function testInput(): void
    {
        $this->assertSame(
            '<input id="input-value" name="input_value" type="text" placeholder="Input Value" />',
            $this->view->Form->input('input_value')
        );
    }

    public function testInputAttributeArray(): void
    {
        $this->assertSame(
            '<input id="input" name="input" data-test="[1,2]" type="text" placeholder="Input" />',
            $this->view->Form->input('input', [
                'data-test' => [1, 2],
            ])
        );
    }

    public function testInputAttributeEscape(): void
    {
        $this->assertSame(
            '<input id="input" name="input" data-test="&lt;test&gt;" type="text" placeholder="Input" />',
            $this->view->Form->input('input', [
                'data-test' => '<test>',
            ])
        );
    }

    public function testInputAttributeInvalid(): void
    {
        $this->assertSame(
            '<input class="test" id="input" name="input" type="text" placeholder="Input" />',
            $this->view->Form->input('input', [
                '*class*' => 'test',
            ])
        );
    }

    public function testInputAttributes(): void
    {
        $this->assertSame(
            '<input class="test" id="other" name="input" type="text" placeholder="Input" />',
            $this->view->Form->input('input', [
                'class' => 'test',
                'id' => 'other',
            ])
        );
    }

    public function testInputAttributesOrder(): void
    {
        $this->assertSame(
            '<input class="test" id="other" name="input" type="text" placeholder="Input" />',
            $this->view->Form->input('input', [
                'id' => 'other',
                'class' => 'test',
            ])
        );
    }

    public function testInputDot(): void
    {
        $this->assertSame(
            '<input id="key-input-value" name="key[input_value]" type="text" placeholder="Input Value" />',
            $this->view->Form->input('key.input_value')
        );
    }

    public function testInputDotDeep(): void
    {
        $this->assertSame(
            '<input id="deep-key-input-value" name="deep[key][input_value]" type="text" placeholder="Input Value" />',
            $this->view->Form->input('deep.key.input_value')
        );
    }

    public function testInputId(): void
    {
        $this->assertSame(
            '<input id="other" name="input" type="text" placeholder="Input" />',
            $this->view->Form->input('input', [
                'id' => 'other',
            ])
        );
    }

    public function testInputIdFalse(): void
    {
        $this->assertSame(
            '<input name="input" type="text" placeholder="Input" />',
            $this->view->Form->input('input', [
                'id' => false,
            ])
        );
    }

    public function testInputIdPrefix(): void
    {
        $this->view->Form->open(idPrefix: 'test');

        $this->assertSame(
            '<input id="test-input" name="input" type="text" placeholder="Input" />',
            $this->view->Form->input('input')
        );
    }

    public function testInputName(): void
    {
        $this->assertSame(
            '<input id="input" name="other" type="text" placeholder="Input" />',
            $this->view->Form->input('input', [
                'name' => 'other',
            ])
        );
    }

    public function testInputNameFalse(): void
    {
        $this->assertSame(
            '<input id="input" type="text" placeholder="Input" />',
            $this->view->Form->input('input', [
                'name' => false,
            ])
        );
    }

    public function testInputPlaceholder(): void
    {
        $this->assertSame(
            '<input id="input" name="input" type="text" placeholder="Other" />',
            $this->view->Form->input('input', [
                'placeholder' => 'Other',
            ])
        );
    }

    public function testInputPlaceholderFalse(): void
    {
        $this->assertSame(
            '<input id="input" name="input" type="text" />',
            $this->view->Form->input('input', [
                'placeholder' => false,
            ])
        );
    }

    public function testInputValueDefault(): void
    {
        $this->assertSame(
            '<input id="input" name="input" type="text" value="test" placeholder="Input" />',
            $this->view->Form->input('input', [
                'default' => 'test',
            ])
        );
    }

    public function testInputValuePost(): void
    {
        Closure::bind(function(): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody([
                'input' => 'test',
            ]);
        }, $this->view, View::class)();

        $this->assertSame(
            '<input id="input" name="input" type="text" value="test" placeholder="Input" />',
            $this->view->Form->input('input')
        );
    }

    public function testInputValuePostDot(): void
    {
        Closure::bind(function(): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody([
                'key' => [
                    'input' => 'test',
                ],
            ]);
        }, $this->view, View::class)();

        $this->assertSame(
            '<input id="key-input" name="key[input]" type="text" value="test" placeholder="Input" />',
            $this->view->Form->input('key.input')
        );
    }

    public function testInputValuePostNull(): void
    {
        Closure::bind(function(): void {
            /** @var View $this */
            $this->request = $this->request->withParsedBody([
                'input' => null,
            ]);
        }, $this->view, View::class)();

        $this->assertSame(
            '<input id="input" name="input" type="text" placeholder="Input" />',
            $this->view->Form->input('input', [
                'default' => 'test',
            ])
        );
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Form;

use Fyre\Form\Rule;

trait FloatTestTrait
{
    public function testFloatBetweenValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'float',
        ]);

        $this->validator->add('value', Rule::between(100, 1000));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="100" max="1000" step="any" />',
            $this->view->Form->input('value')
        );
    }

    public function testFloatFormValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'float',
        ]);

        $this->form->set('value', 100.123);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="100.123" placeholder="Value" step="any" />',
            $this->view->Form->input('value')
        );
    }

    public function testFloatGreaterThanOrEqualsValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'float',
        ]);

        $this->validator->add('value', Rule::greaterThanOrEquals(100));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="100" step="any" />',
            $this->view->Form->input('value')
        );
    }

    public function testFloatGreaterThanValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'float',
        ]);

        $this->validator->add('value', Rule::greaterThan(100));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="101" step="any" />',
            $this->view->Form->input('value')
        );
    }

    public function testFloatLessThanOrEqualsValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'float',
        ]);

        $this->validator->add('value', Rule::lessThanOrEquals(1000));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" max="1000" step="any" />',
            $this->view->Form->input('value')
        );
    }

    public function testFloatLessThanValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'float',
        ]);

        $this->validator->add('value', Rule::lessThan(1000));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" max="999" step="any" />',
            $this->view->Form->input('value')
        );
    }

    public function testFloatMinMaxSchema(): void
    {
        $this->schema->addField('value', [
            'type' => 'float',
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" step="any" />',
            $this->view->Form->input('value')
        );
    }

    public function testFloatRequiredValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'float',
        ]);

        $this->validator->add('value', Rule::required());

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" step="any" required />',
            $this->view->Form->input('value')
        );
    }

    public function testFloatSchemaDefaultValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'float',
            'default' => 100.123,
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="100.123" placeholder="Value" step="any" />',
            $this->view->Form->input('value')
        );
    }
}

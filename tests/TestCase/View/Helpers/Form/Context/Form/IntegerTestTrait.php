<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Form;

use Fyre\Form\Rule;

trait IntegerTestTrait
{
    public function testIntegerBetweenValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'integer',
        ]);

        $this->validator->add('value', Rule::between(100, 1000));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="100" max="1000" step="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testIntegerFormValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'integer',
        ]);

        $this->form->set('value', 999);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="999" placeholder="Value" step="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testIntegerGreaterThanOrEqualsValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'integer',
        ]);

        $this->validator->add('value', Rule::greaterThanOrEquals(100));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="100" step="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testIntegerGreaterThanValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'integer',
        ]);

        $this->validator->add('value', Rule::greaterThan(100));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="101" step="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testIntegerLessThanOrEqualsValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'integer',
        ]);

        $this->validator->add('value', Rule::lessThanOrEquals(1000));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" max="1000" step="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testIntegerLessThanValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'integer',
        ]);

        $this->validator->add('value', Rule::lessThan(1000));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" max="999" step="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testIntegerMinMaxSchema(): void
    {
        $this->schema->addField('value', [
            'type' => 'integer',
            'precision' => 10,
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="-9999999999" max="9999999999" step="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testIntegerRequiredValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'integer',
        ]);

        $this->validator->add('value', Rule::required());

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" step="1" required />',
            $this->view->Form->input('value')
        );
    }

    public function testIntegerSchemaDefaultValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'integer',
            'default' => 999,
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="999" placeholder="Value" step="1" />',
            $this->view->Form->input('value')
        );
    }
}

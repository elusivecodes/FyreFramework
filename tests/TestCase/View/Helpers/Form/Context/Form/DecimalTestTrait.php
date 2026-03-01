<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Form;

use Fyre\Form\Rule;

trait DecimalTestTrait
{
    public function testDecimalBetweenValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
        ]);

        $this->validator->add('value', Rule::between(100, 1000));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="100" max="1000" step="0.01" />',
            $this->view->Form->input('value')
        );
    }

    public function testDecimalFormValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
        ]);

        $this->form->set('value', 100.99);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="100.99" placeholder="Value" min="-99999999.99" max="99999999.99" step="0.01" />',
            $this->view->Form->input('value')
        );
    }

    public function testDecimalGreaterThanOrEqualsValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
        ]);

        $this->validator->add('value', Rule::greaterThanOrEquals(100));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="100" max="99999999.99" step="0.01" />',
            $this->view->Form->input('value')
        );
    }

    public function testDecimalGreaterThanValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
        ]);

        $this->validator->add('value', Rule::greaterThan(100));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="101" max="99999999.99" step="0.01" />',
            $this->view->Form->input('value')
        );
    }

    public function testDecimalLessThanOrEqualsValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
        ]);

        $this->validator->add('value', Rule::lessThanOrEquals(1000));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="-99999999.99" max="1000" step="0.01" />',
            $this->view->Form->input('value')
        );
    }

    public function testDecimalLessThanValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
        ]);

        $this->validator->add('value', Rule::lessThan(1000));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="-99999999.99" max="999" step="0.01" />',
            $this->view->Form->input('value')
        );
    }

    public function testDecimalMinMaxSchema(): void
    {
        $this->schema->addField('value', [
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="-99999999.99" max="99999999.99" step="0.01" />',
            $this->view->Form->input('value')
        );
    }

    public function testDecimalRequiredValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
        ]);

        $this->validator->add('value', Rule::required());

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="-99999999.99" max="99999999.99" step="0.01" required />',
            $this->view->Form->input('value')
        );
    }

    public function testDecimalSchemaDefaultValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
            'default' => 100.99,
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="100.99" placeholder="Value" min="-99999999.99" max="99999999.99" step="0.01" />',
            $this->view->Form->input('value')
        );
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Form;

use Fyre\Form\Rule;
use Fyre\Utility\DateTime\DateTime;

trait DateTestTrait
{
    public function testDateFormValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'date',
        ]);

        $this->form->set('value', DateTime::createFromArray([2022, 1, 1]));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="date" value="2022-01-01" />',
            $this->view->Form->input('value')
        );
    }

    public function testDateRequiredValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'date',
        ]);

        $this->validator->add('value', Rule::required());

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="date" required />',
            $this->view->Form->input('value')
        );
    }

    public function testDateSchema(): void
    {
        $this->schema->addField('value', [
            'type' => 'date',
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="date" />',
            $this->view->Form->input('value')
        );
    }

    public function testDateSchemaDefaultValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'date',
            'default' => '2022-01-01',
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="date" value="2022-01-01" />',
            $this->view->Form->input('value')
        );
    }
}

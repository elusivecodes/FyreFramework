<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Form;

use Fyre\Form\Rule;
use Fyre\Utility\DateTime\DateTime;

trait DateTimeTestTrait
{
    public function testDatetimeFormValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'datetime',
        ]);

        $this->form->set('value', DateTime::createFromArray([2022, 1, 1]));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="datetime-local" value="2022-01-01T00:00" />',
            $this->view->Form->input('value')
        );
    }

    public function testDatetimeRequiredValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'datetime',
        ]);

        $this->validator->add('value', Rule::required());

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="datetime-local" required />',
            $this->view->Form->input('value')
        );
    }

    public function testDatetimeSchema(): void
    {
        $this->schema->addField('value', [
            'type' => 'datetime',
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="datetime-local" />',
            $this->view->Form->input('value')
        );
    }

    public function testDatetimeSchemaDefaultValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'datetime',
            'default' => '2022-01-01 00:00:00',
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="datetime-local" value="2022-01-01T00:00" />',
            $this->view->Form->input('value')
        );
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Form;

use Fyre\Form\Rule;
use Fyre\Utility\DateTime\DateTime;

trait TimeTestTrait
{
    public function testTimeFormValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'time',
        ]);

        $this->form->set('value', DateTime::createFromArray([2022, 1, 1, 12, 30]));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="time" value="12:30" />',
            $this->view->Form->input('value')
        );
    }

    public function testTimeRequiredValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'time',
        ]);

        $this->validator->add('value', Rule::required());

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="time" required />',
            $this->view->Form->input('value')
        );
    }

    public function testTimeSchema(): void
    {
        $this->schema->addField('value', [
            'type' => 'time',
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="time" />',
            $this->view->Form->input('value')
        );
    }

    public function testTimeSchemaDefaultValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'time',
            'default' => '12:30:00',
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="time" value="12:30" />',
            $this->view->Form->input('value')
        );
    }
}

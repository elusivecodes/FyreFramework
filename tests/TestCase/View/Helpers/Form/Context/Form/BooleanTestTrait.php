<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Form;

use Fyre\Form\Rule;

trait BooleanTestTrait
{
    public function testBooleanFormValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'boolean',
        ]);

        $this->form->set('value', true);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input name="value" type="hidden" value="0" /><input id="value" name="value" type="checkbox" value="1" checked />',
            $this->view->Form->input('value')
        );
    }

    public function testBooleanRequiredValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'boolean',
        ]);

        $this->validator->add('value', Rule::required());

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input name="value" type="hidden" value="0" /><input id="value" name="value" type="checkbox" value="1" required />',
            $this->view->Form->input('value')
        );
    }

    public function testBooleanSchema(): void
    {
        $this->schema->addField('value', [
            'type' => 'boolean',
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input name="value" type="hidden" value="0" /><input id="value" name="value" type="checkbox" value="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testBooleanSchemaDefaultValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'boolean',
            'default' => true,
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input name="value" type="hidden" value="0" /><input id="value" name="value" type="checkbox" value="1" checked />',
            $this->view->Form->input('value')
        );
    }
}

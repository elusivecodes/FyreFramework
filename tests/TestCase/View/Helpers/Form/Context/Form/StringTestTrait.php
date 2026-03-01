<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Form;

use Fyre\Form\Rule;

trait StringTestTrait
{
    public function testStringEntityValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'string',
            'length' => 255,
        ]);

        $this->form->set('value', 'Test');

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="text" value="Test" placeholder="Value" maxlength="255" />',
            $this->view->Form->input('value')
        );
    }

    public function testStringMaxLengthSchema(): void
    {
        $this->schema->addField('value', [
            'type' => 'string',
            'length' => 255,
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="text" placeholder="Value" maxlength="255" />',
            $this->view->Form->input('value')
        );
    }

    public function testStringMaxLengthValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'string',
            'length' => 255,
        ]);

        $this->validator->add('value', Rule::maxLength(100));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="text" placeholder="Value" maxlength="100" />',
            $this->view->Form->input('value')
        );
    }

    public function testStringRequiredValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'string',
            'length' => 255,
        ]);

        $this->validator->add('value', Rule::required());

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="text" placeholder="Value" required maxlength="255" />',
            $this->view->Form->input('value')
        );
    }

    public function testStringSchemaDefaultValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'string',
            'length' => 255,
            'default' => 'Test',
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="text" value="Test" placeholder="Value" maxlength="255" />',
            $this->view->Form->input('value')
        );
    }
}

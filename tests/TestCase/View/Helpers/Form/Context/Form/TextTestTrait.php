<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Form;

use Fyre\Form\Rule;

trait TextTestTrait
{
    public function testTextFormValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'text',
            'length' => 65535,
        ]);

        $this->form->set('value', 'Test');

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<textarea id="value" name="value" placeholder="Value" maxlength="65535">Test</textarea>',
            $this->view->Form->input('value')
        );
    }

    public function testTextMaxLengthSchema(): void
    {
        $this->schema->addField('value', [
            'type' => 'text',
            'length' => 65535,
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<textarea id="value" name="value" placeholder="Value" maxlength="65535"></textarea>',
            $this->view->Form->input('value')
        );
    }

    public function testTextMaxLengthValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'text',
            'length' => 65535,
        ]);

        $this->validator->add('value', Rule::maxLength(1000));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<textarea id="value" name="value" placeholder="Value" maxlength="1000"></textarea>',
            $this->view->Form->input('value')
        );
    }

    public function testTextRequiredValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'text',
            'length' => 65535,
        ]);

        $this->validator->add('value', Rule::required());

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<textarea id="value" name="value" placeholder="Value" required maxlength="65535"></textarea>',
            $this->view->Form->input('value')
        );
    }

    public function testTextSchemaDefaultValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'text',
            'length' => 65535,
            'default' => 'Test',
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<textarea id="value" name="value" placeholder="Value" maxlength="65535">Test</textarea>',
            $this->view->Form->input('value')
        );
    }
}

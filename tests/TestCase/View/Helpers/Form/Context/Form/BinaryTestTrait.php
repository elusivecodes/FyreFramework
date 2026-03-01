<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Form;

use Fyre\Form\Rule;

trait BinaryTestTrait
{
    public function testBinaryRequiredValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'binary',
        ]);

        $this->validator->add('value', Rule::required());

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="file" required />',
            $this->view->Form->input('value')
        );
    }
}

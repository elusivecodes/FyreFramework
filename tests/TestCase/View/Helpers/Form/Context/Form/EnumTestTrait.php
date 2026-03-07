<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Form;

use Tests\Mock\Enums\Status;

trait EnumTestTrait
{
    public function testEnumField(): void
    {
        $this->schema
            ->addField('status', ['type' => 'string'])
            ->setEnumClass('status', Status::class);

        $this->form->set('status', Status::Published);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<select id="status" name="status"><option value="draft">Draft label</option><option value="published" selected>Published label</option></select>',
            $this->view->Form->input('status')
        );
    }
}

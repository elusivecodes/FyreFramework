<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

trait FieldsetCloseTestTrait
{
    public function testFieldsetClose(): void
    {
        $this->assertSame(
            '</fieldset>',
            $this->view->Form->fieldsetClose()
        );
    }
}

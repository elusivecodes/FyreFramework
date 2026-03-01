<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FormBuilder;

trait FieldsetCloseTestTrait
{
    public function testFieldsetClose(): void
    {
        $this->assertSame(
            '</fieldset>',
            $this->form->fieldsetClose()
        );
    }
}

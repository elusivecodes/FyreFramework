<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FormBuilder;

trait CloseTestTrait
{
    public function testClose(): void
    {
        $this->assertSame(
            '</form>',
            $this->form->close()
        );
    }
}

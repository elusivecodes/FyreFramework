<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use PHPUnit\Framework\AssertionFailedError;

trait FlashMessageEqualsTrait
{
    public function testFlashMessageEquals(): void
    {
        $this->get('/flash');

        $this->assertFlashMessage('value', 'key');
    }

    public function testFlashMessageEqualsFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that session flash message "key" value is equal to \'value\'.');

        $this->get('/response');

        $this->assertFlashMessage('value', 'key');
    }

    public function testFlashMessageEqualsNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that session flash message "key" value is equal to \'value\'.');

        $this->assertFlashMessage('value', 'key');
    }
}

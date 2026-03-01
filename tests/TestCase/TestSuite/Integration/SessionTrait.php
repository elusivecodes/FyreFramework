<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use PHPUnit\Framework\AssertionFailedError;

trait SessionTrait
{
    public function testSession(): void
    {
        $this->get('/session');

        $this->assertSession('value', 'key');
    }

    public function testSessionFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that session "key" value is equal to \'value\'.');

        $this->get('/response');

        $this->assertSession('value', 'key');
    }

    public function testSessionNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that session "key" value is equal to \'value\'.');

        $this->assertSession('value', 'key');
    }
}

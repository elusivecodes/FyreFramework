<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use PHPUnit\Framework\AssertionFailedError;

trait SessionHasKeyTrait
{
    public function testSessionHasKey(): void
    {
        $this->get('/session');

        $this->assertSessionHasKey('key');
    }

    public function testSessionHasKeyFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that session has the key \'key\'.');

        $this->get('/response');

        $this->assertSessionHasKey('key');
    }

    public function testSessionHasKeyNoResponse(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that session has the key \'key\'.');

        $this->assertSessionHasKey('key');
    }

    public function testSessionNotHasKey(): void
    {
        $this->get('/response');

        $this->assertSessionNotHasKey('key');
    }

    public function testSessionNotHasKeyFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that session does not have the key \'key\'.');

        $this->get('/session');

        $this->assertSessionNotHasKey('key');
    }

    public function testSessionNotHasKeyNoResponse(): void
    {
        $this->assertSessionNotHasKey('key');
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

use InvalidArgumentException;

trait PriorityTestTrait
{
    public function testHeaderPriority(): void
    {
        $this->email->setPriority(1);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            1,
            $headers['X-Priority']
        );
    }

    public function testSetPriority(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setPriority(1)
        );

        $this->assertSame(
            1,
            $this->email->getPriority()
        );
    }

    public function testSetPriorityInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email priority must be between 1 and 5.');

        $this->email->setPriority(0);
    }

    public function testSetPriorityInvalidMaximum(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email priority must be between 1 and 5.');

        $this->email->setPriority(6);
    }

    public function testSetPriorityNull(): void
    {
        $this->email->setPriority(null);

        $this->assertNull(
            $this->email->getPriority()
        );
    }
}

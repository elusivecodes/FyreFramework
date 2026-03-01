<?php
declare(strict_types=1);

namespace Tests\TestCase\Queue;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Queue\Message;
use Fyre\Queue\Worker;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Jobs\MockJob;

use function class_uses;

final class MessageTest extends TestCase
{
    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Worker::class)
        );
    }

    public function testMessageHash(): void
    {
        $message1 = new Message([
            'className' => MockJob::class,
            'method' => 'run',
            'arguments' => [
                'test' => 1,
                'other' => 2,
            ],
        ]);

        $message2 = new Message([
            'className' => MockJob::class,
            'method' => 'run',
            'arguments' => [
                'other' => 2,
                'test' => 1,
            ],
        ]);

        $this->assertSame(
            'ee46d2557c41fcac20b2c66ec902d5a0',
            $message1->getHash()
        );

        $this->assertSame(
            $message1->getHash(),
            $message2->getHash()
        );
    }

    public function testMessageUnique(): void
    {
        $message = new Message([
            'unique' => true,
        ]);

        $this->assertTrue(
            $message->isUnique()
        );
    }
}

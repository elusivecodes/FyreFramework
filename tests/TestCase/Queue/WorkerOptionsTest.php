<?php
declare(strict_types=1);

namespace Tests\TestCase\Queue;

use Fyre\Core\Container;
use Fyre\Event\EventManager;
use Fyre\Queue\QueueManager;
use Fyre\Queue\Worker;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class WorkerOptionsTest extends TestCase
{
    /**
     * @return array<string, array{array<string, int>, string}>
     */
    public static function invalidOptionsProvider(): array
    {
        return [
            'max jobs' => [
                ['maxJobs' => -1],
                'Worker option `maxJobs` must not be negative.',
            ],
            'max runtime' => [
                ['maxRuntime' => -1],
                'Worker option `maxRuntime` must not be negative.',
            ],
            'rest' => [
                ['rest' => -1],
                'Worker option `rest` must not be negative.',
            ],
            'sleep' => [
                ['sleep' => -1],
                'Worker option `sleep` must not be negative.',
            ],
        ];
    }

    /**
     * @param array<string, int> $options
     */
    #[DataProvider('invalidOptionsProvider')]
    public function testInvalidOptions(array $options, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs($message);

        new Worker(
            new Container(),
            $this->createStub(QueueManager::class),
            $this->createStub(EventManager::class),
            $options
        );
    }
}

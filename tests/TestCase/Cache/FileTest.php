<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Handlers\File\FileCacher;
use Fyre\Cache\TaggedCacher;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\TestCase\Cache\Cacher\DecrementTestTrait;
use Tests\TestCase\Cache\Cacher\DeleteTestTrait;
use Tests\TestCase\Cache\Cacher\EmptyTestTrait;
use Tests\TestCase\Cache\Cacher\GetSetTestTrait;
use Tests\TestCase\Cache\Cacher\HasTestTrait;
use Tests\TestCase\Cache\Cacher\IncrementTestTrait;
use Tests\TestCase\Cache\Cacher\LockTestTrait;
use Tests\TestCase\Cache\Cacher\RememberTestTrait;
use Tests\TestCase\Cache\Cacher\TagsTestTrait;
use Throwable;

use function class_uses;
use function function_exists;
use function mkdir;
use function pcntl_fork;
use function pcntl_waitpid;
use function rmdir;
use function usleep;

final class FileTest extends TestCase
{
    use DecrementTestTrait;
    use DeleteTestTrait;
    use EmptyTestTrait;
    use GetSetTestTrait;
    use HasTestTrait;
    use IncrementTestTrait;
    use LockTestTrait;
    use RememberTestTrait;
    use TagsTestTrait;

    protected Cacher $cacher;

    public function testConcurrentSynchronizedUpdates(): void
    {
        if (!function_exists('pcntl_fork')) {
            $this->markTestSkipped('The pcntl extension is not available.');
        }

        $this->cacher->set('counter', 0);

        $processes = [];

        for ($i = 0; $i < 5; $i++) {
            $process = pcntl_fork();

            if ($process === 0) {
                try {
                    for ($j = 0; $j < 20; $j++) {
                        $this->cacher->synchronized(
                            'counter',
                            function(): void {
                                $count = $this->cacher->get('counter', 0);

                                usleep(1000);

                                $this->cacher->set('counter', $count + 1);
                            },
                            wait: 5
                        );
                    }

                    exit(0);
                } catch (Throwable) {
                    exit(1);
                }
            }

            $this->assertGreaterThan(
                0,
                $process
            );

            $processes[] = $process;
        }

        foreach ($processes as $process) {
            pcntl_waitpid($process, $status);

            $this->assertSame(
                0,
                $status
            );
        }

        $this->assertSame(
            100,
            $this->cacher->get('counter')
        );
    }

    public function testDebug(): void
    {
        $data = $this->cacher->__debugInfo();

        $this->assertSame(
            [
                '[class]' => FileCacher::class,
                'config' => [
                    'expire' => null,
                    'prefix' => 'prefix.',
                    'path' => '[*****]',
                    'mode' => 0640,
                    'className' => FileCacher::class,
                ],
                'path' => '[*****]',
            ],
            $data
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(TaggedCacher::class)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        @mkdir('cache');

        $this->cacher = new Container()
            ->use(CacheManager::class)
            ->build([
                'className' => FileCacher::class,
                'path' => 'cache',
                'prefix' => 'prefix.',
            ]);
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->cacher->clear();
        @rmdir('cache');
    }
}

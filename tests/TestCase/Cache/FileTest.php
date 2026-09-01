<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache;

use Closure;
use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Handlers\File\FileCacher;
use Fyre\Core\Container;
use Override;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
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

use function file_put_contents;
use function mkdir;
use function pcntl_fork;
use function pcntl_waitpid;
use function rmdir;
use function serialize;
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

    #[RequiresPhpExtension('pcntl')]
    public function testConcurrentSynchronizedUpdates(): void
    {
        $this->cacher->set('counter', 0);

        $updateCounter = function(): void {
            for ($i = 0; $i < 20; $i++) {
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
        };

        $process = pcntl_fork();

        if ($process === 0) {
            try {
                $updateCounter();
                exit(0);
            } catch (Throwable) {
                exit(1);
            }
        }

        $this->assertGreaterThan(
            0,
            $process
        );

        $updateCounter();

        pcntl_waitpid($process, $status);

        $this->assertSame(
            0,
            $status
        );

        $this->assertSame(
            40,
            $this->cacher->get('counter')
        );
    }

    public function testDebug(): void
    {
        $data = $this->cacher->__debugInfo();

        $this->assertArraysAreIdentical(
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
    }

    public function testExpiredValue(): void
    {
        Closure::bind(function(): void {
            /** @var FileCacher $this */
            $this->setValue('prefix.test', 'value', -1);
        }, $this->cacher, FileCacher::class)();

        $this->assertNull(
            $this->cacher->get('test')
        );
    }

    public function testLockFileRemovedOnRelease(): void
    {
        $lock = $this->cacher->lock('test');

        $this->assertTrue(
            $lock->acquire()
        );

        $this->assertFileExists('cache/prefix.__lock__.test');

        $this->assertTrue(
            $lock->release()
        );

        $this->assertFileDoesNotExist('cache/prefix.__lock__.test');
    }

    public function testMalformedLock(): void
    {
        file_put_contents('cache/prefix.__lock__.test', 'invalid');

        $lock = $this->cacher->lock('test');

        $this->assertTrue(
            $lock->acquire()
        );
        $this->assertTrue(
            $lock->release()
        );
    }

    public function testMalformedValue(): void
    {
        file_put_contents('cache/prefix.test', 'invalid');

        $this->assertSame(
            'default',
            $this->cacher->get('test', 'default')
        );
    }

    public function testMalformedValueIncrement(): void
    {
        file_put_contents('cache/prefix.test', serialize([
            'data' => 1,
        ]));

        $this->assertSame(
            2,
            $this->cacher->increment('test', 2)
        );
        $this->assertSame(
            2,
            $this->cacher->get('test')
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

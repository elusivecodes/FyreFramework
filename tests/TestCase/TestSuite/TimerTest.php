<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite;

use Fyre\Core\Traits\DebugTrait;
use Fyre\TestSuite\Timer;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;
use function usleep;

final class TimerTest extends TestCase
{
    protected Timer $timer;

    public function testCount(): void
    {
        $this->timer->start('test1');
        $this->timer->start('test2');

        $this->assertSame(2, $this->timer->count());
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Timer::class)
        );
    }

    public function testElapsed(): void
    {
        $this->timer->start('test');
        usleep(500000);
        $elapsed1 = $this->timer->elapsed('test');
        usleep(500000);
        $elapsed2 = $this->timer->elapsed('test');

        $this->assertGreaterThan($elapsed1, $elapsed2);
        $this->assertFalse($this->timer->isStopped('test'));
    }

    public function testElapsedInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timer `test` does not exist.');

        $this->timer->elapsed('test');
    }

    public function testGet(): void
    {
        $this->timer->start('test');

        $timer = $this->timer->get('test');

        $this->assertArrayHasKey('start', $timer);
        $this->assertArrayHasKey('end', $timer);
        $this->assertArrayHasKey('duration', $timer);
        $this->assertIsInt($timer['start']);
        $this->assertNull($timer['end']);
        $this->assertNull($timer['duration']);
    }

    public function testGetInvalid(): void
    {
        $this->assertNull($this->timer->get('test'));
    }

    public function testHasFalse(): void
    {
        $this->assertFalse($this->timer->has('test'));
    }

    public function testHasTrue(): void
    {
        $this->timer->start('test');

        $this->assertTrue($this->timer->has('test'));
    }

    public function testIsStoppedFalse(): void
    {
        $this->timer->start('test');

        $this->assertFalse($this->timer->isStopped('test'));
    }

    public function testIsStoppedInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timer `test` does not exist.');

        $this->timer->isStopped('test');
    }

    public function testIsStoppedTrue(): void
    {
        $this->timer->start('test');
        $this->timer->stop('test');

        $this->assertTrue($this->timer->isStopped('test'));
    }

    public function testRemove(): void
    {
        $this->timer->start('test');

        $this->assertSame(
            $this->timer,
            $this->timer->remove('test')
        );

        $this->assertFalse($this->timer->has('test'));
    }

    public function testRemoveInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timer `test` does not exist.');

        $this->timer->remove('test');
    }

    public function testStart(): void
    {
        $this->assertSame(
            $this->timer,
            $this->timer->start('test')
        );
    }

    public function testStartMultiple(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timer `test` has already been started.');

        $this->timer->start('test');
        $this->timer->start('test');
    }

    public function testStop(): void
    {
        $this->timer->start('test');

        $this->assertSame(
            $this->timer,
            $this->timer->stop('test')
        );

        $timer = $this->timer->get('test');

        $this->assertArrayHasKey('start', $timer);
        $this->assertArrayHasKey('end', $timer);
        $this->assertArrayHasKey('duration', $timer);
        $this->assertIsInt($timer['start']);
        $this->assertIsInt($timer['end']);
        $this->assertIsFloat($timer['duration']);
    }

    public function testStopAll(): void
    {
        $this->timer->start('test1');
        $this->timer->start('test2');

        $this->assertSame(
            $this->timer,
            $this->timer->stopAll()
        );

        $timers = $this->timer->all();

        $this->assertCount(2, $timers);
        $this->assertArrayHasKey('test1', $timers);
        $this->assertArrayHasKey('test2', $timers);
        $this->assertSame($this->timer->get('test1'), $timers['test1']);
        $this->assertSame($this->timer->get('test2'), $timers['test2']);
    }

    public function testStopAllMultiple(): void
    {
        $this->timer->start('test1');
        $this->timer->start('test2');
        $this->timer->stopAll();

        $timers = $this->timer->all();

        usleep(500000);
        $this->timer->stopAll();

        $this->assertSame($timers, $this->timer->all());
    }

    public function testStopInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timer `test` does not exist.');

        $this->timer->stop('test');
    }

    public function testStopMultiple(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timer `test` has already been stopped.');

        $this->timer->start('test');
        $this->timer->stop('test');
        $this->timer->stop('test');
    }

    #[Override]
    protected function setUp(): void
    {
        $this->timer = new Timer();
    }
}

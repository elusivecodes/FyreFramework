<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite;

use Fyre\Core\Traits\DebugTrait;
use Fyre\TestSuite\Benchmark;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class BenchmarkTest extends TestCase
{
    protected Benchmark $benchmark;

    public function testAdd(): void
    {
        $this->assertSame(
            $this->benchmark,
            $this->benchmark->add('test1', static function(): void {})
        );
    }

    public function testAll(): void
    {
        $test1 = static function(): void {};
        $test2 = static function(): void {};

        $this->benchmark->add('test1', $test1);
        $this->benchmark->add('test2', $test2);

        $tests = $this->benchmark->all();

        $this->assertIsArray($tests);
        $this->assertCount(2, $tests);
        $this->assertArrayHasKey('test1', $tests);
        $this->assertArrayHasKey('test2', $tests);
        $this->assertSame($test1, $tests['test1']);
        $this->assertSame($test2, $tests['test2']);
    }

    public function testCount(): void
    {
        $this->benchmark->add('test1', static function(): void {});
        $this->benchmark->add('test2', static function(): void {});

        $this->assertSame(2, $this->benchmark->count());
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Benchmark::class)
        );
    }

    public function testGet(): void
    {
        $test = static function(): void {};

        $this->benchmark->add('test', $test);

        $this->assertSame($test, $this->benchmark->get('test'));
    }

    public function testGetInvalid(): void
    {
        $this->assertNull($this->benchmark->get('test'));
    }

    public function testHasFalse(): void
    {
        $this->benchmark->add('test', static function(): void {});

        $this->assertFalse($this->benchmark->has('invalid'));
    }

    public function testHasTrue(): void
    {
        $this->benchmark->add('test', static function(): void {});

        $this->assertTrue($this->benchmark->has('test'));
    }

    public function testRemove(): void
    {
        $this->benchmark->add('test1', static function(): void {});
        $this->benchmark->add('test2', static function(): void {});

        $this->assertSame(
            $this->benchmark,
            $this->benchmark->remove('test1')
        );

        $this->assertFalse($this->benchmark->has('test1'));
        $this->assertTrue($this->benchmark->has('test2'));
    }

    public function testRemoveInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Test `invalid` does not exist.');

        $this->benchmark->remove('invalid');
    }

    public function testRun(): void
    {
        $i = 0;
        $this->benchmark->add('test', static function() use (&$i): void {
            $i++;
        });

        $results = $this->benchmark->run();

        $this->assertSame(1000, $i);

        $this->assertArrayHasKey('test', $results);
        $this->assertArrayHasKey('time', $results['test']);
        $this->assertArrayHasKey('memory', $results['test']);
        $this->assertArrayHasKey('n', $results['test']);
        $this->assertIsFloat($results['test']['time']);
        $this->assertIsInt($results['test']['memory']);
        $this->assertSame(1000, $results['test']['n']);
    }

    public function testRunMultipleTests(): void
    {
        $i = 0;
        $j = 0;
        $this->benchmark->add('test1', static function() use (&$i): void {
            $i++;
        });
        $this->benchmark->add('test2', static function() use (&$j): void {
            $j++;
        });

        $results = $this->benchmark->run();

        $this->assertSame(1000, $i);
        $this->assertSame(1000, $j);

        $this->assertArrayHasKey('test1', $results);
        $this->assertArrayHasKey('test2', $results);
        $this->assertArrayHasKey('time', $results['test1']);
        $this->assertArrayHasKey('memory', $results['test1']);
        $this->assertArrayHasKey('n', $results['test1']);
        $this->assertArrayHasKey('time', $results['test2']);
        $this->assertArrayHasKey('memory', $results['test2']);
        $this->assertArrayHasKey('n', $results['test2']);
        $this->assertIsFloat($results['test1']['time']);
        $this->assertIsInt($results['test1']['memory']);
        $this->assertSame(1000, $results['test1']['n']);
        $this->assertIsFloat($results['test2']['time']);
        $this->assertIsInt($results['test2']['memory']);
        $this->assertSame(1000, $results['test2']['n']);
    }

    public function testRunWithIterations(): void
    {
        $i = 0;
        $this->benchmark->add('test', static function() use (&$i): void {
            $i++;
        });

        $this->benchmark->run(500);

        $this->assertSame(500, $i);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->benchmark = new Benchmark();
    }
}

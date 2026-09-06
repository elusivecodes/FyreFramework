<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite;

use Fyre\Core\Traits\DebugTrait;
use Fyre\TestSuite\Benchmark;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function class_uses;

final class BenchmarkTest extends TestCase
{
    protected Benchmark $benchmark;

    /**
     * @return array<string, array{list<string>, string}>
     */
    public static function resultProvider(): array
    {
        return [
            'single test' => [['test'], 'test'],
            'first of multiple tests' => [['test1', 'test2'], 'test1'],
            'second of multiple tests' => [['test1', 'test2'], 'test2'],
        ];
    }

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
        $this->expectExceptionMessageIs('Test `invalid` does not exist.');

        $this->benchmark->remove('invalid');
    }

    public function testRun(): void
    {
        $i = 0;
        $this->benchmark->add('test', static function() use (&$i): void {
            $i++;
        });

        $this->benchmark->run();

        $this->assertSame(1000, $i);
    }

    public function testRunMultipleResults(): void
    {
        $this->benchmark->add('test1', static function(): void {});
        $this->benchmark->add('test2', static function(): void {});

        $results = $this->benchmark->run() |> array_keys(...);

        $this->assertArraysAreIdentical(['test1', 'test2'], $results);
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

        $this->benchmark->run();

        $this->assertSame(1000, $i);
        $this->assertSame(1000, $j);
    }

    /**
     * @param list<string> $names
     */
    #[DataProvider('resultProvider')]
    public function testRunResultMetadata(array $names, string $name): void
    {
        foreach ($names as $testName) {
            $this->benchmark->add($testName, static function(): void {});
        }

        $result = $this->benchmark->run()[$name];

        $this->assertIsFloat($result['time']);
        $this->assertIsInt($result['memory']);
        $this->assertSame(1000, $result['n']);
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

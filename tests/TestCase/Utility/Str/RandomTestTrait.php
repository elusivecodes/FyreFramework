<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use InvalidArgumentException;

use function array_unique;
use function count;

trait RandomTestTrait
{
    public function testRandomIsRandom(): void
    {
        $strings = [];

        for ($i = 0; $i < 1000; $i++) {
            $string = Str::random();
            $this->assertMatchesRegularExpression(
                '/^[0-9a-zA-Z]{16}$/',
                $string
            );
            $strings[] = $string;
        }

        $strings = array_unique($strings);

        $this->assertGreaterThan(
            100,
            count($strings)
        );
    }

    public function testRandomWithAlphaChars(): void
    {
        $strings = [];

        for ($i = 0; $i < 1000; $i++) {
            $string = Str::random(8, Str::ALPHA);
            $this->assertMatchesRegularExpression(
                '/^[a-zA-Z]{8}$/',
                $string
            );
            $strings[] = $string;
        }

        $strings = array_unique($strings);

        $this->assertGreaterThan(
            100,
            count($strings)
        );
    }

    public function testRandomWithInvalidChars(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Random string characters must not be empty.');

        Str::random(8, '');
    }

    public function testRandomWithInvalidLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Random string length must be greater than or equal to 0.');

        Str::random(-1);
    }

    public function testRandomWithLength(): void
    {
        $strings = [];

        for ($i = 0; $i < 1000; $i++) {
            $string = Str::random(24);
            $this->assertMatchesRegularExpression(
                '/^[0-9a-zA-Z]{24}$/',
                $string
            );
            $strings[] = $string;
        }

        $strings = array_unique($strings);

        $this->assertGreaterThan(
            100,
            count($strings)
        );
    }

    public function testRandomWithNumericChars(): void
    {
        $strings = [];

        for ($i = 0; $i < 1000; $i++) {
            $string = Str::random(8, Str::NUMERIC);
            $this->assertMatchesRegularExpression(
                '/^[0-9]{8}$/',
                $string
            );
            $strings[] = $string;
        }

        $strings = array_unique($strings);

        $this->assertGreaterThan(
            100,
            count($strings)
        );
    }
}

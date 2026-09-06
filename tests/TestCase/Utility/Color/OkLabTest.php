<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Color;

use Fyre\Utility\Color\Colors\OkLab;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OkLabTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function conversionProvider(): array
    {
        return [
            'A98Rgb' => ['toA98Rgb', 'color(a98-rgb 0.9 0.9 0.98)'],
            'DisplayP3' => ['toDisplayP3', 'color(display-p3 0.9 0.9 0.97)'],
            'DisplayP3Linear' => ['toDisplayP3Linear', 'color(display-p3-linear 0.79 0.79 0.94)'],
            'Hex' => ['toHex', '#e6e6fa'],
            'Hsl' => ['toHsl', 'hsl(240deg 66.67% 94.12%)'],
            'Hwb' => ['toHwb', 'hwb(240deg 90.2% 1.96%)'],
            'Lab' => ['toLab', 'lab(91.74% 2.78 -9.72)'],
            'Lch' => ['toLch', 'lch(91.74% 10.11 285.93deg)'],
            'OkLch' => ['toOkLch', 'oklch(0.93 0.03 285.86deg)'],
            'ProPhotoRgb' => ['toProPhotoRgb', 'color(prophoto-rgb 0.89 0.88 0.96)'],
            'Rec2020' => ['toRec2020', 'color(rec2020 0.91 0.91 0.97)'],
            'Rgb' => ['toRgb', 'rgb(230 230 250)'],
            'Srgb' => ['toSrgb', 'color(srgb 0.9 0.9 0.98)'],
            'SrgbLinear' => ['toSrgbLinear', 'color(srgb-linear 0.79 0.79 0.96)'],
            'XyzD50' => ['toXyzD50', 'color(xyz-d50 0.79 0.8 0.77)'],
            'XyzD65' => ['toXyzD65', 'color(xyz-d65 0.78 0.8 1.02)'],
        ];
    }

    public function testConstructorClamping(): void
    {
        $color = new OkLab(3, -1, 1, 1.5);

        $this->assertSame(
            'oklab(3 -1 1)',
            $color->toString()
        );
    }

    public function testContrast(): void
    {
        $color1 = OkLab::createFromString('lavender');
        $color2 = OkLab::createFromString('black');

        $this->assertSame(17.063750102904255, $color1->contrast($color2));
        $this->assertSame(17.063750102904255, $color2->contrast($color1));
    }

    #[DataProvider('conversionProvider')]
    public function testConversion(string $method, string $expected): void
    {
        $color = OkLab::createFromString('lavender');
        $converted = $color->$method();

        $this->assertNotSame($color, $converted);

        $this->assertSame(
            $expected,
            $converted->toString()
        );
    }

    public function testGetA(): void
    {
        $color = OkLab::createFromString('lavender');

        $this->assertSame(0.0073649318907060835, $color->getA());
    }

    public function testGetB(): void
    {
        $color = OkLab::createFromString('lavender');

        $this->assertSame(-0.025915245880047233, $color->getB());
    }

    public function testGetLightness(): void
    {
        $color = OkLab::createFromString('lavender');

        $this->assertSame(0.9309023355374633, $color->getLightness());
    }

    public function testLabel(): void
    {
        $color = OkLab::createFromString('lavender')->withLightness(0.5);

        $this->assertSame('dimgray', $color->label());
    }

    public function testLuma(): void
    {
        $color = OkLab::createFromString('lavender');

        $this->assertSame(0.8031875051452128, $color->luma());
    }

    public function testSpace(): void
    {
        $color = new OkLab();

        $this->assertSame('oklab', $color->space());
    }

    public function testToArray(): void
    {
        $color = OkLab::createFromString('lavender');

        $this->assertArraysAreIdentical(
            [
                'lightness' => 0.9309023355374633,
                'a' => 0.0073649318907060835,
                'b' => -0.025915245880047233,
                'alpha' => 1.0,
            ],
            $color->toArray()
        );
    }

    public function testToOkLab(): void
    {
        $color1 = OkLab::createFromString('lavender');
        $color2 = $color1->toOkLab();

        $this->assertSame(
            $color1,
            $color2
        );
    }

    public function testToString(): void
    {
        $color = OkLab::createFromString('lavender');

        $this->assertSame(
            'oklab(0.93 0.01 -0.03)',
            $color->toString()
        );

        $this->assertSame(
            'oklab(0.93 0.01 -0.03)',
            (string) $color
        );
    }

    public function testToStringWithAlpha(): void
    {
        $color = OkLab::createFromString('lavender')->withAlpha(0.5);

        $this->assertSame(
            'oklab(0.93 0.01 -0.03 / 0.5)',
            $color->toString()
        );
    }

    public function testWithA(): void
    {
        $color1 = OkLab::createFromString('lavender');
        $color2 = $color1->withA(0.2);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'oklab(0.93 0.2 -0.03)',
            $color2->toString()
        );
    }

    public function testWithB(): void
    {
        $color1 = OkLab::createFromString('lavender');
        $color2 = $color1->withB(0.2);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'oklab(0.93 0.01 0.2)',
            $color2->toString()
        );
    }

    public function testWithLightness(): void
    {
        $color1 = OkLab::createFromString('lavender');
        $color2 = $color1->withLightness(0.5);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'oklab(0.5 0.01 -0.03)',
            $color2->toString()
        );
    }
}

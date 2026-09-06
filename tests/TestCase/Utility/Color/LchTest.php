<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Color;

use Fyre\Utility\Color\Colors\Lch;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LchTest extends TestCase
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
            'OkLab' => ['toOkLab', 'oklab(0.93 0.01 -0.03)'],
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
        $color = new Lch(150, -150, -30, 1.5);

        $this->assertSame(
            'lch(150% -150 330deg)',
            $color->toString()
        );
    }

    public function testContrast(): void
    {
        $color1 = Lch::createFromString('lavender');
        $color2 = Lch::createFromString('black');

        $this->assertSame(17.063750102904255, $color1->contrast($color2));
        $this->assertSame(17.063750102904255, $color2->contrast($color1));
    }

    #[DataProvider('conversionProvider')]
    public function testConversion(string $method, string $expected): void
    {
        $color = Lch::createFromString('lavender');
        $converted = $color->$method();

        $this->assertNotSame($color, $converted);

        $this->assertSame(
            $expected,
            $converted->toString()
        );
    }

    public function testGetChroma(): void
    {
        $color = Lch::createFromString('lavender');

        $this->assertSame(10.112556083083701, $color->getChroma());
    }

    public function testGetHue(): void
    {
        $color = Lch::createFromString('lavender');

        $this->assertSame(285.9285772969358, $color->getHue());
    }

    public function testGetLightness(): void
    {
        $color = Lch::createFromString('lavender');

        $this->assertSame(91.74228613147233, $color->getLightness());
    }

    public function testLabel(): void
    {
        $color = Lch::createFromString('lavender')->withLightness(50);

        $this->assertSame('slategray', $color->label());
    }

    public function testLuma(): void
    {
        $color = Lch::createFromString('lavender');

        $this->assertSame(0.8031875051452128, $color->luma());
    }

    public function testSpace(): void
    {
        $color = new Lch();

        $this->assertSame('lch', $color->space());
    }

    public function testToArray(): void
    {
        $color = Lch::createFromString('lavender');

        $this->assertArraysAreIdentical(
            [
                'lightness' => 91.74228613147233,
                'chroma' => 10.112556083083701,
                'hue' => 285.9285772969358,
                'alpha' => 1.0,
            ],
            $color->toArray()
        );
    }

    public function testToLch(): void
    {
        $color1 = Lch::createFromString('lavender');
        $color2 = $color1->toLch();

        $this->assertSame(
            $color1,
            $color2
        );
    }

    public function testToString(): void
    {
        $color = Lch::createFromString('lavender');

        $this->assertSame(
            'lch(91.74% 10.11 285.93deg)',
            $color->toString()
        );

        $this->assertSame(
            'lch(91.74% 10.11 285.93deg)',
            (string) $color
        );
    }

    public function testToStringWithAlpha(): void
    {
        $color = Lch::createFromString('lavender')->withAlpha(0.5);

        $this->assertSame(
            'lch(91.74% 10.11 285.93deg / 0.5)',
            $color->toString()
        );
    }

    public function testWithChroma(): void
    {
        $color1 = Lch::createFromString('lavender');
        $color2 = $color1->withChroma(50);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'lch(91.74% 50 285.93deg)',
            $color2->toString()
        );
    }

    public function testWithHue(): void
    {
        $color1 = Lch::createFromString('lavender');
        $color2 = $color1->withHue(100);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'lch(91.74% 10.11 100deg)',
            $color2->toString()
        );
    }

    public function testWithLightness(): void
    {
        $color1 = Lch::createFromString('lavender');
        $color2 = $color1->withLightness(50);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'lch(50% 10.11 285.93deg)',
            $color2->toString()
        );
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Color;

use Fyre\Utility\Color\Colors\OkLch;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OkLchTest extends TestCase
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
            'OkLab' => ['toOkLab', 'oklab(0.93 0.01 -0.03)'],
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
        $color = new OkLch(3, -1, -30, 1.5);

        $this->assertSame(
            'oklch(3 -1 330deg)',
            $color->toString()
        );
    }

    public function testContrast(): void
    {
        $color1 = OkLch::createFromString('lavender');
        $color2 = OkLch::createFromString('black');

        $this->assertSame(17.063750102904255, $color1->contrast($color2));
        $this->assertSame(17.063750102904255, $color2->contrast($color1));
    }

    #[DataProvider('conversionProvider')]
    public function testConversion(string $method, string $expected): void
    {
        $color = OkLch::createFromString('lavender');
        $converted = $color->$method();

        $this->assertNotSame($color, $converted);

        $this->assertSame(
            $expected,
            $converted->toString()
        );
    }

    public function testGetChroma(): void
    {
        $color = OkLch::createFromString('lavender');

        $this->assertSame(0.02694145858668466, $color->getChroma());
    }

    public function testGetHue(): void
    {
        $color = OkLch::createFromString('lavender');

        $this->assertSame(285.86477952157645, $color->getHue());
    }

    public function testGetLightness(): void
    {
        $color = OkLch::createFromString('lavender');

        $this->assertSame(0.9309023355374633, $color->getLightness());
    }

    public function testLabel(): void
    {
        $color = OkLch::createFromString('lavender')->withLightness(0.5);

        $this->assertSame('darkslateblue', $color->label());
    }

    public function testLuma(): void
    {
        $color = OkLch::createFromString('lavender');

        $this->assertSame(0.8031875051452128, $color->luma());
    }

    public function testSpace(): void
    {
        $color = new OkLch();

        $this->assertSame('oklch', $color->space());
    }

    public function testToArray(): void
    {
        $color = OkLch::createFromString('lavender');

        $this->assertArraysAreIdentical(
            [
                'lightness' => 0.9309023355374633,
                'chroma' => 0.02694145858668466,
                'hue' => 285.86477952157645,
                'alpha' => 1.0,
            ],
            $color->toArray()
        );
    }

    public function testToOkLch(): void
    {
        $color1 = OkLch::createFromString('lavender');
        $color2 = $color1->toOkLch();

        $this->assertSame(
            $color1,
            $color2
        );
    }

    public function testToString(): void
    {
        $color = OkLch::createFromString('lavender');

        $this->assertSame(
            'oklch(0.93 0.03 285.86deg)',
            $color->toString()
        );

        $this->assertSame(
            'oklch(0.93 0.03 285.86deg)',
            (string) $color
        );
    }

    public function testToStringWithAlpha(): void
    {
        $color = OkLch::createFromString('lavender')->withAlpha(0.5);

        $this->assertSame(
            'oklch(0.93 0.03 285.86deg / 0.5)',
            $color->toString()
        );
    }

    public function testWithChroma(): void
    {
        $color1 = OkLch::createFromString('lavender');
        $color2 = $color1->withChroma(0.2);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'oklch(0.93 0.2 285.86deg)',
            $color2->toString()
        );
    }

    public function testWithHue(): void
    {
        $color1 = OkLch::createFromString('lavender');
        $color2 = $color1->withHue(100);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'oklch(0.93 0.03 100deg)',
            $color2->toString()
        );
    }

    public function testWithLightness(): void
    {
        $color1 = OkLch::createFromString('lavender');
        $color2 = $color1->withLightness(0.5);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'oklch(0.5 0.03 285.86deg)',
            $color2->toString()
        );
    }
}

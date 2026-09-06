<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Color;

use Fyre\Utility\Color\Colors\Rgb;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RgbTest extends TestCase
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
            'OkLch' => ['toOkLch', 'oklch(0.93 0.03 285.86deg)'],
            'ProPhotoRgb' => ['toProPhotoRgb', 'color(prophoto-rgb 0.89 0.88 0.96)'],
            'Rec2020' => ['toRec2020', 'color(rec2020 0.91 0.91 0.97)'],
            'Srgb' => ['toSrgb', 'color(srgb 0.9 0.9 0.98)'],
            'SrgbLinear' => ['toSrgbLinear', 'color(srgb-linear 0.79 0.79 0.96)'],
            'XyzD50' => ['toXyzD50', 'color(xyz-d50 0.79 0.8 0.77)'],
            'XyzD65' => ['toXyzD65', 'color(xyz-d65 0.78 0.8 1.02)'],
        ];
    }

    public function testConstructorClamping(): void
    {
        $color = new Rgb(300, -20, 500, 1.5);

        $this->assertSame(
            'rgb(300 -20 500)',
            $color->toString()
        );
    }

    public function testContrast(): void
    {
        $color1 = Rgb::createFromString('lavender');
        $color2 = Rgb::createFromString('black');

        $this->assertSame(17.063750102904258, $color1->contrast($color2));
        $this->assertSame(17.063750102904258, $color2->contrast($color1));
    }

    #[DataProvider('conversionProvider')]
    public function testConversion(string $method, string $expected): void
    {
        $color = Rgb::createFromString('lavender');
        $converted = $color->$method();

        $this->assertNotSame($color, $converted);

        $this->assertSame(
            $expected,
            $converted->toString()
        );
    }

    public function testGetBlue(): void
    {
        $color = Rgb::createFromString('lavender');

        $this->assertSame(250.0, $color->getBlue());
    }

    public function testGetGreen(): void
    {
        $color = Rgb::createFromString('lavender');

        $this->assertSame(230.0, $color->getGreen());
    }

    public function testGetRed(): void
    {
        $color = Rgb::createFromString('lavender');

        $this->assertSame(230.0, $color->getRed());
    }

    public function testLabel(): void
    {
        $color = Rgb::createFromString('lavender')->withGreen(100);

        $this->assertSame('violet', $color->label());
    }

    public function testLuma(): void
    {
        $color = Rgb::createFromString('lavender');

        $this->assertSame(0.8031875051452129, $color->luma());
    }

    public function testSpace(): void
    {
        $color = new Rgb();

        $this->assertSame('rgb', $color->space());
    }

    public function testToArray(): void
    {
        $color = Rgb::createFromString('lavender');

        $this->assertArraysAreIdentical(
            [
                'red' => 230.0,
                'green' => 230.0,
                'blue' => 250.0,
                'alpha' => 1.0,
            ],
            $color->toArray()
        );
    }

    public function testToRgb(): void
    {
        $color1 = Rgb::createFromString('lavender');
        $color2 = $color1->toRgb();

        $this->assertSame(
            $color1,
            $color2
        );
    }

    public function testToString(): void
    {
        $color = Rgb::createFromString('lavender');

        $this->assertSame(
            'rgb(230 230 250)',
            $color->toString()
        );

        $this->assertSame(
            'rgb(230 230 250)',
            (string) $color
        );
    }

    public function testToStringName(): void
    {
        $color = Rgb::createFromString('lavender');

        $this->assertSame(
            'lavender',
            $color->toString(name: true)
        );
    }

    public function testToStringWithAlpha(): void
    {
        $color = Rgb::createFromString('lavender')->withAlpha(0.5);

        $this->assertSame(
            'rgb(230 230 250 / 50%)',
            $color->toString()
        );
    }

    public function testWithBlue(): void
    {
        $color1 = Rgb::createFromString('lavender');
        $color2 = $color1->withBlue(100);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'rgb(230 230 100)',
            $color2->toString()
        );
    }

    public function testWithGreen(): void
    {
        $color1 = Rgb::createFromString('lavender');
        $color2 = $color1->withGreen(100);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'rgb(230 100 250)',
            $color2->toString()
        );
    }

    public function testWithRed(): void
    {
        $color1 = Rgb::createFromString('lavender');
        $color2 = $color1->withRed(100);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'rgb(100 230 250)',
            $color2->toString()
        );
    }
}

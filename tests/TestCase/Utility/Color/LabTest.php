<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Color;

use Fyre\Utility\Color\Colors\Lab;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LabTest extends TestCase
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
            'Lch' => ['toLch', 'lch(91.74% 10.11 285.93deg)'],
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
        $color = new Lab(150, -150, 300, 1.5);

        $this->assertSame(
            'lab(150% -150 300)',
            $color->toString()
        );
    }

    public function testContrast(): void
    {
        $color1 = Lab::createFromString('lavender');
        $color2 = Lab::createFromString('black');

        $this->assertSame(17.063750102904255, $color1->contrast($color2));
        $this->assertSame(17.063750102904255, $color2->contrast($color1));
    }

    #[DataProvider('conversionProvider')]
    public function testConversion(string $method, string $expected): void
    {
        $color = Lab::createFromString('lavender');
        $converted = $color->$method();

        $this->assertNotSame($color, $converted);

        $this->assertSame(
            $expected,
            $converted->toString()
        );
    }

    public function testGetA(): void
    {
        $color = Lab::createFromString('lavender');

        $this->assertSame(2.775278467403053, $color->getA());
    }

    public function testGetB(): void
    {
        $color = Lab::createFromString('lavender');

        $this->assertSame(-9.724279919967449, $color->getB());
    }

    public function testGetLightness(): void
    {
        $color = Lab::createFromString('lavender');

        $this->assertSame(91.74228613147233, $color->getLightness());
    }

    public function testLabel(): void
    {
        $color = Lab::createFromString('lavender')->withLightness(50);

        $this->assertSame('slategray', $color->label());
    }

    public function testLuma(): void
    {
        $color = Lab::createFromString('lavender');

        $this->assertSame(0.8031875051452128, $color->luma());
    }

    public function testSpace(): void
    {
        $color = new Lab();

        $this->assertSame('lab', $color->space());
    }

    public function testToArray(): void
    {
        $color = Lab::createFromString('lavender');

        $this->assertArraysAreIdentical(
            [
                'lightness' => 91.74228613147233,
                'a' => 2.775278467403053,
                'b' => -9.724279919967449,
                'alpha' => 1.0,
            ],
            $color->toArray()
        );
    }

    public function testToLab(): void
    {
        $color1 = Lab::createFromString('lavender');
        $color2 = $color1->toLab();

        $this->assertSame(
            $color1,
            $color2
        );
    }

    public function testToString(): void
    {
        $color = Lab::createFromString('lavender');

        $this->assertSame(
            'lab(91.74% 2.78 -9.72)',
            $color->toString()
        );

        $this->assertSame(
            'lab(91.74% 2.78 -9.72)',
            (string) $color
        );
    }

    public function testToStringWithAlpha(): void
    {
        $color = Lab::createFromString('lavender')->withAlpha(0.5);

        $this->assertSame(
            'lab(91.74% 2.78 -9.72 / 0.5)',
            $color->toString()
        );
    }

    public function testWithA(): void
    {
        $color1 = Lab::createFromString('lavender');
        $color2 = $color1->withA(50);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'lab(91.74% 50 -9.72)',
            $color2->toString()
        );
    }

    public function testWithB(): void
    {
        $color1 = Lab::createFromString('lavender');
        $color2 = $color1->withB(50);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'lab(91.74% 2.78 50)',
            $color2->toString()
        );
    }

    public function testWithLightness(): void
    {
        $color1 = Lab::createFromString('lavender');
        $color2 = $color1->withLightness(50);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'lab(50% 2.78 -9.72)',
            $color2->toString()
        );
    }
}

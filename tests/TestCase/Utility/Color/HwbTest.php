<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Color;

use Fyre\Utility\Color\Colors\Hwb;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HwbTest extends TestCase
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
            'Lab' => ['toLab', 'lab(91.74% 2.78 -9.72)'],
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
        $color = new Hwb(390, -100, 300, 1.5);

        $this->assertSame(
            'hwb(30deg -100% 300%)',
            $color->toString()
        );
    }

    public function testContrast(): void
    {
        $color1 = Hwb::createFromString('lavender');
        $color2 = Hwb::createFromString('black');

        $this->assertSame(17.063750102904258, $color1->contrast($color2));
        $this->assertSame(17.063750102904258, $color2->contrast($color1));
    }

    #[DataProvider('conversionProvider')]
    public function testConversion(string $method, string $expected): void
    {
        $color = Hwb::createFromString('lavender');
        $converted = $color->$method();

        $this->assertNotSame($color, $converted);

        $this->assertSame(
            $expected,
            $converted->toString()
        );
    }

    public function testGetBlackness(): void
    {
        $color = Hwb::createFromString('lavender');

        $this->assertSame(1.9607843137254943, $color->getBlackness());
    }

    public function testGetHue(): void
    {
        $color = Hwb::createFromString('lavender');

        $this->assertSame(240.0, $color->getHue());
    }

    public function testGetWhiteness(): void
    {
        $color = Hwb::createFromString('lavender');

        $this->assertSame(90.19607843137256, $color->getWhiteness());
    }

    public function testLabel(): void
    {
        $color = Hwb::createFromString('lavender')->withWhiteness(50);

        $this->assertSame('mediumslateblue', $color->label());
    }

    public function testLuma(): void
    {
        $color = Hwb::createFromString('lavender');

        $this->assertSame(0.8031875051452129, $color->luma());
    }

    public function testSpace(): void
    {
        $color = new Hwb();

        $this->assertSame('hwb', $color->space());
    }

    public function testToArray(): void
    {
        $color = Hwb::createFromString('lavender');

        $this->assertArraysAreIdentical(
            [
                'hue' => 240.0,
                'whiteness' => 90.19607843137256,
                'blackness' => 1.9607843137254943,
                'alpha' => 1.0,
            ],
            $color->toArray()
        );
    }

    public function testToHwb(): void
    {
        $color1 = Hwb::createFromString('lavender');
        $color2 = $color1->toHwb();

        $this->assertSame(
            $color1,
            $color2
        );
    }

    public function testToString(): void
    {
        $color = Hwb::createFromString('lavender');

        $this->assertSame(
            'hwb(240deg 90.2% 1.96%)',
            $color->toString()
        );

        $this->assertSame(
            'hwb(240deg 90.2% 1.96%)',
            (string) $color
        );
    }

    public function testToStringWithAlpha(): void
    {
        $color = Hwb::createFromString('lavender')->withAlpha(0.5);

        $this->assertSame(
            'hwb(240deg 90.2% 1.96% / 50%)',
            $color->toString()
        );
    }

    public function testWithBlackness(): void
    {
        $color1 = Hwb::createFromString('lavender');
        $color2 = $color1->withBlackness(50);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'hwb(240deg 90.2% 50%)',
            $color2->toString()
        );
    }

    public function testWithHue(): void
    {
        $color1 = Hwb::createFromString('lavender');
        $color2 = $color1->withHue(100);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'hwb(100deg 90.2% 1.96%)',
            $color2->toString()
        );
    }

    public function testWithWhiteness(): void
    {
        $color1 = Hwb::createFromString('lavender');
        $color2 = $color1->withWhiteness(50);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'hwb(240deg 50% 1.96%)',
            $color2->toString()
        );
    }
}

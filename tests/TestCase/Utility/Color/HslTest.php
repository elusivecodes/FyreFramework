<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Color;

use Fyre\Utility\Color\Colors\Hsl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HslTest extends TestCase
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
            'Hwb' => ['toHwb', 'hwb(240deg 90.2% 1.96%)'],
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
        $color = new Hsl(390, -100, 300, 1.5);

        $this->assertSame(
            'hsl(30deg -100% 300%)',
            $color->toString()
        );
    }

    public function testContrast(): void
    {
        $color1 = Hsl::createFromString('lavender');
        $color2 = Hsl::createFromString('black');

        $this->assertSame(17.06375010290425, $color1->contrast($color2));
        $this->assertSame(17.06375010290425, $color2->contrast($color1));
    }

    #[DataProvider('conversionProvider')]
    public function testConversion(string $method, string $expected): void
    {
        $color = Hsl::createFromString('lavender');
        $converted = $color->$method();

        $this->assertNotSame($color, $converted);

        $this->assertSame(
            $expected,
            $converted->toString()
        );
    }

    public function testGetHue(): void
    {
        $color = Hsl::createFromString('lavender');

        $this->assertSame(240.0, $color->getHue());
    }

    public function testGetLightness(): void
    {
        $color = Hsl::createFromString('lavender');

        $this->assertSame(94.11764705882352, $color->getLightness());
    }

    public function testGetSaturation(): void
    {
        $color = Hsl::createFromString('lavender');

        $this->assertSame(66.66666666666666, $color->getSaturation());
    }

    public function testLabel(): void
    {
        $color = Hsl::createFromString('lavender')->withSaturation(50);

        $this->assertSame('lavender', $color->label());
    }

    public function testLuma(): void
    {
        $color = Hsl::createFromString('lavender');

        $this->assertSame(0.8031875051452125, $color->luma());
    }

    public function testSpace(): void
    {
        $color = new Hsl();

        $this->assertSame('hsl', $color->space());
    }

    public function testToArray(): void
    {
        $color = Hsl::createFromString('lavender');

        $this->assertArraysAreIdentical(
            [
                'hue' => 240.0,
                'saturation' => 66.66666666666666,
                'lightness' => 94.11764705882352,
                'alpha' => 1.0,
            ],
            $color->toArray()
        );
    }

    public function testToHsl(): void
    {
        $color1 = Hsl::createFromString('lavender');
        $color2 = $color1->toHsl();

        $this->assertSame(
            $color1,
            $color2
        );
    }

    public function testToString(): void
    {
        $color = Hsl::createFromString('lavender');

        $this->assertSame(
            'hsl(240deg 66.67% 94.12%)',
            $color->toString()
        );

        $this->assertSame(
            'hsl(240deg 66.67% 94.12%)',
            (string) $color
        );
    }

    public function testToStringWithAlpha(): void
    {
        $color = Hsl::createFromString('lavender')->withAlpha(0.5);

        $this->assertSame(
            'hsl(240deg 66.67% 94.12% / 50%)',
            $color->toString()
        );
    }

    public function testWithHue(): void
    {
        $color1 = Hsl::createFromString('lavender');
        $color2 = $color1->withHue(100);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'hsl(100deg 66.67% 94.12%)',
            $color2->toString()
        );
    }

    public function testWithLightness(): void
    {
        $color1 = Hsl::createFromString('lavender');
        $color2 = $color1->withLightness(50);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'hsl(240deg 66.67% 50%)',
            $color2->toString()
        );
    }

    public function testWithSaturation(): void
    {
        $color1 = Hsl::createFromString('lavender');
        $color2 = $color1->withSaturation(50);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'hsl(240deg 50% 94.12%)',
            $color2->toString()
        );
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Color;

use Fyre\Utility\Color\Colors\XyzD65;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class XyzD65Test extends TestCase
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
            'Rgb' => ['toRgb', 'rgb(230 230 250)'],
            'Srgb' => ['toSrgb', 'color(srgb 0.9 0.9 0.98)'],
            'SrgbLinear' => ['toSrgbLinear', 'color(srgb-linear 0.79 0.79 0.96)'],
            'XyzD50' => ['toXyzD50', 'color(xyz-d50 0.79 0.8 0.77)'],
        ];
    }

    public function testConstructorClamping(): void
    {
        $color = new XyzD65(2, -1, 3, 1.5);

        $this->assertSame(
            'color(xyz-d65 2 -1 3)',
            $color->toString()
        );
    }

    public function testContrast(): void
    {
        $color1 = XyzD65::createFromString('lavender');
        $color2 = XyzD65::createFromString('black');

        $this->assertSame(17.06375010290425, $color1->contrast($color2));
        $this->assertSame(17.06375010290425, $color2->contrast($color1));
    }

    #[DataProvider('conversionProvider')]
    public function testConversion(string $method, string $expected): void
    {
        $color = XyzD65::createFromString('lavender');
        $converted = $color->$method();

        $this->assertNotSame($color, $converted);

        $this->assertSame(
            $expected,
            $converted->toString()
        );
    }

    public function testGetX(): void
    {
        $color = XyzD65::createFromString('lavender');

        $this->assertSame(0.7818145658065243, $color->getX());
    }

    public function testGetY(): void
    {
        $color = XyzD65::createFromString('lavender');

        $this->assertSame(0.8031862396740685, $color->getY());
    }

    public function testGetZ(): void
    {
        $color = XyzD65::createFromString('lavender');

        $this->assertSame(1.0182984297418491, $color->getZ());
    }

    public function testLabel(): void
    {
        $color = XyzD65::createFromString('lavender')->withY(0.5);

        $this->assertSame('violet', $color->label());
    }

    public function testLuma(): void
    {
        $color = XyzD65::createFromString('lavender');

        $this->assertSame(0.8031875051452126, $color->luma());
    }

    public function testSpace(): void
    {
        $color = new XyzD65();

        $this->assertSame('xyz-d65', $color->space());
    }

    public function testToArray(): void
    {
        $color = XyzD65::createFromString('lavender');

        $this->assertArraysAreIdentical(
            [
                'x' => 0.7818145658065243,
                'y' => 0.8031862396740685,
                'z' => 1.0182984297418491,
                'alpha' => 1.0,
            ],
            $color->toArray()
        );
    }

    public function testToString(): void
    {
        $color = XyzD65::createFromString('lavender');

        $this->assertSame(
            'color(xyz-d65 0.78 0.8 1.02)',
            $color->toString()
        );

        $this->assertSame(
            'color(xyz-d65 0.78 0.8 1.02)',
            (string) $color
        );
    }

    public function testToStringWithAlpha(): void
    {
        $color = XyzD65::createFromString('lavender')->withAlpha(0.5);

        $this->assertSame(
            'color(xyz-d65 0.78 0.8 1.02 / 0.5)',
            $color->toString()
        );
    }

    public function testToXyzD65(): void
    {
        $color1 = XyzD65::createFromString('lavender');
        $color2 = $color1->toXyzD65();

        $this->assertSame(
            $color1,
            $color2
        );
    }

    public function testWithX(): void
    {
        $color1 = XyzD65::createFromString('lavender');
        $color2 = $color1->withX(0.5);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'color(xyz-d65 0.5 0.8 1.02)',
            $color2->toString()
        );
    }

    public function testWithY(): void
    {
        $color1 = XyzD65::createFromString('lavender');
        $color2 = $color1->withY(0.5);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'color(xyz-d65 0.78 0.5 1.02)',
            $color2->toString()
        );
    }

    public function testWithZ(): void
    {
        $color1 = XyzD65::createFromString('lavender');
        $color2 = $color1->withZ(0.5);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'color(xyz-d65 0.78 0.8 0.5)',
            $color2->toString()
        );
    }
}

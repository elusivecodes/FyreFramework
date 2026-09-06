<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Color;

use Fyre\Utility\Color\Colors\Rec2020;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Rec2020Test extends TestCase
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
            'Rgb' => ['toRgb', 'rgb(230 230 250)'],
            'Srgb' => ['toSrgb', 'color(srgb 0.9 0.9 0.98)'],
            'SrgbLinear' => ['toSrgbLinear', 'color(srgb-linear 0.79 0.79 0.96)'],
            'XyzD50' => ['toXyzD50', 'color(xyz-d50 0.79 0.8 0.77)'],
            'XyzD65' => ['toXyzD65', 'color(xyz-d65 0.78 0.8 1.02)'],
        ];
    }

    public function testConstructorClamping(): void
    {
        $color = new Rec2020(2, -1, 3, 1.5);

        $this->assertSame(
            'color(rec2020 2 -1 3)',
            $color->toString()
        );
    }

    public function testContrast(): void
    {
        $color1 = Rec2020::createFromString('lavender');
        $color2 = Rec2020::createFromString('black');

        $this->assertSame(17.06375010290425, $color1->contrast($color2));
        $this->assertSame(17.06375010290425, $color2->contrast($color1));
    }

    #[DataProvider('conversionProvider')]
    public function testConversion(string $method, string $expected): void
    {
        $color = Rec2020::createFromString('lavender');
        $converted = $color->$method();

        $this->assertNotSame($color, $converted);

        $this->assertSame(
            $expected,
            $converted->toString()
        );
    }

    public function testGetBlue(): void
    {
        $color = Rec2020::createFromString('lavender');

        $this->assertSame(0.9740210670993709, $color->getBlue());
    }

    public function testGetGreen(): void
    {
        $color = Rec2020::createFromString('lavender');

        $this->assertSame(0.9079649060658369, $color->getGreen());
    }

    public function testGetRed(): void
    {
        $color = Rec2020::createFromString('lavender');

        $this->assertSame(0.9104696523917976, $color->getRed());
    }

    public function testLabel(): void
    {
        $color = Rec2020::createFromString('lavender')->withGreen(0.5);

        $this->assertSame('violet', $color->label());
    }

    public function testLuma(): void
    {
        $color = Rec2020::createFromString('lavender');

        $this->assertSame(0.8031875051452125, $color->luma());
    }

    public function testSpace(): void
    {
        $color = new Rec2020();

        $this->assertSame('rec2020', $color->space());
    }

    public function testToArray(): void
    {
        $color = Rec2020::createFromString('lavender');

        $this->assertArraysAreIdentical(
            [
                'red' => 0.9104696523917976,
                'green' => 0.9079649060658369,
                'blue' => 0.9740210670993709,
                'alpha' => 1.0,
            ],
            $color->toArray()
        );
    }

    public function testToRec2020(): void
    {
        $color1 = Rec2020::createFromString('lavender');
        $color2 = $color1->toRec2020();

        $this->assertSame(
            $color1,
            $color2
        );
    }

    public function testToString(): void
    {
        $color = Rec2020::createFromString('lavender');

        $this->assertSame(
            'color(rec2020 0.91 0.91 0.97)',
            $color->toString()
        );

        $this->assertSame(
            'color(rec2020 0.91 0.91 0.97)',
            (string) $color
        );
    }

    public function testToStringWithAlpha(): void
    {
        $color = Rec2020::createFromString('lavender')->withAlpha(0.5);

        $this->assertSame(
            'color(rec2020 0.91 0.91 0.97 / 0.5)',
            $color->toString()
        );
    }

    public function testWithBlue(): void
    {
        $color1 = Rec2020::createFromString('lavender');
        $color2 = $color1->withBlue(0.5);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'color(rec2020 0.91 0.91 0.5)',
            $color2->toString()
        );
    }

    public function testWithGreen(): void
    {
        $color1 = Rec2020::createFromString('lavender');
        $color2 = $color1->withGreen(0.5);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'color(rec2020 0.91 0.5 0.97)',
            $color2->toString()
        );
    }

    public function testWithRed(): void
    {
        $color1 = Rec2020::createFromString('lavender');
        $color2 = $color1->withRed(0.5);

        $this->assertNotSame(
            $color1,
            $color2
        );

        $this->assertSame(
            'color(rec2020 0.5 0.91 0.97)',
            $color2->toString()
        );
    }
}

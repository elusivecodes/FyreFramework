<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Color;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Core\Traits\StaticMacroTrait;
use Fyre\Utility\Color\Color;
use Fyre\Utility\Color\Colors\A98Rgb;
use Fyre\Utility\Color\Colors\DisplayP3;
use Fyre\Utility\Color\Colors\DisplayP3Linear;
use Fyre\Utility\Color\Colors\Hsl;
use Fyre\Utility\Color\Colors\Hwb;
use Fyre\Utility\Color\Colors\Lab;
use Fyre\Utility\Color\Colors\Lch;
use Fyre\Utility\Color\Colors\OkLab;
use Fyre\Utility\Color\Colors\OkLch;
use Fyre\Utility\Color\Colors\ProPhotoRgb;
use Fyre\Utility\Color\Colors\Rec2020;
use Fyre\Utility\Color\Colors\Rgb;
use Fyre\Utility\Color\Colors\Srgb;
use Fyre\Utility\Color\Colors\SrgbLinear;
use Fyre\Utility\Color\Colors\XyzD50;
use Fyre\Utility\Color\Colors\XyzD65;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function class_uses;
use function serialize;
use function unserialize;

final class ColorTest extends TestCase
{
    public function testCreateFromA98Rgb(): void
    {
        $color = Color::createFromA98Rgb(0.9, 0.9, 0.98);

        $this->assertInstanceOf(A98Rgb::class, $color);
        $this->assertSame('color(a98-rgb 0.9 0.9 0.98)', $color->toString());
    }

    public function testCreateFromDisplayP3(): void
    {
        $color = Color::createFromDisplayP3(0.9, 0.9, 0.97);

        $this->assertInstanceOf(DisplayP3::class, $color);
        $this->assertSame('color(display-p3 0.9 0.9 0.97)', $color->toString());
    }

    public function testCreateFromDisplayP3Linear(): void
    {
        $color = Color::createFromDisplayP3Linear(0.79, 0.79, 0.94);

        $this->assertInstanceOf(DisplayP3Linear::class, $color);
        $this->assertSame('color(display-p3-linear 0.79 0.79 0.94)', $color->toString());
    }

    public function testCreateFromHsl(): void
    {
        $color = Color::createFromHsl(240, 66.67, 94.12);

        $this->assertInstanceOf(Hsl::class, $color);
        $this->assertSame('hsl(240deg 66.67% 94.12%)', $color->toString());
    }

    public function testCreateFromHwb(): void
    {
        $color = Color::createFromHwb(120, 90.2, 1.96);

        $this->assertInstanceOf(Hwb::class, $color);
        $this->assertSame('hwb(120deg 90.2% 1.96%)', $color->toString());
    }

    public function testCreateFromLab(): void
    {
        $color = Color::createFromLab(91.74, 2.78, -9.72);

        $this->assertInstanceOf(Lab::class, $color);
        $this->assertSame('lab(91.74% 2.78 -9.72)', $color->toString());
    }

    public function testCreateFromLch(): void
    {
        $color = Color::createFromLch(91.74, 10.11, 285.93);

        $this->assertInstanceOf(Lch::class, $color);
        $this->assertSame('lch(91.74% 10.11 285.93deg)', $color->toString());
    }

    public function testCreateFromOkLab(): void
    {
        $color = Color::createFromOkLab(0.93, 0.01, -0.03);

        $this->assertInstanceOf(OkLab::class, $color);
        $this->assertSame('oklab(0.93 0.01 -0.03)', $color->toString());
    }

    public function testCreateFromOkLch(): void
    {
        $color = Color::createFromOkLch(0.93, 0.03, 285.8);

        $this->assertInstanceOf(OkLch::class, $color);
        $this->assertSame('oklch(0.93 0.03 285.8deg)', $color->toString());
    }

    public function testCreateFromProPhotoRgb(): void
    {
        $color = Color::createFromProPhotoRgb(0.89, 0.88, 0.96);

        $this->assertInstanceOf(ProPhotoRgb::class, $color);
        $this->assertSame('color(prophoto-rgb 0.89 0.88 0.96)', $color->toString());
    }

    public function testCreateFromRec2020(): void
    {
        $color = Color::createFromRec2020(0.89, 0.89, 0.97);

        $this->assertInstanceOf(Rec2020::class, $color);
        $this->assertSame('color(rec2020 0.89 0.89 0.97)', $color->toString());
    }

    public function testCreateFromRgb(): void
    {
        $color = Color::createFromRgb(230, 230, 250);

        $this->assertInstanceOf(Rgb::class, $color);
        $this->assertSame('rgb(230 230 250)', $color->toString());
    }

    public function testCreateFromSrgb(): void
    {
        $color = Color::createFromSrgb(0.9, 0.9, 0.98);

        $this->assertInstanceOf(Srgb::class, $color);
        $this->assertSame('color(srgb 0.9 0.9 0.98)', $color->toString());
    }

    public function testCreateFromSrgbLinear(): void
    {
        $color = Color::createFromSrgbLinear(0.79, 0.79, 0.96);

        $this->assertInstanceOf(SrgbLinear::class, $color);
        $this->assertSame('color(srgb-linear 0.79 0.79 0.96)', $color->toString());
    }

    public function testCreateFromStringA98Rgb(): void
    {
        $color = Color::createFromString('color(a98-rgb 0.9 0.9 0.98)');

        $this->assertInstanceOf(A98Rgb::class, $color);
        $this->assertSame('color(a98-rgb 0.9 0.9 0.98)', $color->toString());
    }

    public function testCreateFromStringA98RgbPercent(): void
    {
        $color = Color::createFromString('color(a98-rgb 90% 90% 98%)');

        $this->assertInstanceOf(A98Rgb::class, $color);
        $this->assertSame('color(a98-rgb 0.9 0.9 0.98)', $color->toString());
    }

    public function testCreateFromStringColorPercentMapping(): void
    {
        $color = Color::createFromString('color(display-p3 25% 50% 75%)');

        $this->assertInstanceOf(DisplayP3::class, $color);
        $this->assertSame('color(display-p3 0.25 0.5 0.75)', $color->toString());
    }

    public function testCreateFromStringDisplayP3(): void
    {
        $color = Color::createFromString('color(display-p3 0.9 0.9 0.97)');

        $this->assertInstanceOf(DisplayP3::class, $color);
        $this->assertSame('color(display-p3 0.9 0.9 0.97)', $color->toString());
    }

    public function testCreateFromStringDisplayP3Linear(): void
    {
        $color = Color::createFromString('color(display-p3-linear 0.79 0.79 0.94)');

        $this->assertInstanceOf(DisplayP3Linear::class, $color);
        $this->assertSame('color(display-p3-linear 0.79 0.79 0.94)', $color->toString());
    }

    public function testCreateFromStringDisplayP3LinearPercent(): void
    {
        $color = Color::createFromString('color(display-p3-linear 79% 79% 94%)');

        $this->assertInstanceOf(DisplayP3Linear::class, $color);
        $this->assertSame('color(display-p3-linear 0.79 0.79 0.94)', $color->toString());
    }

    public function testCreateFromStringDisplayP3Percent(): void
    {
        $color = Color::createFromString('color(display-p3 90% 90% 97%)');

        $this->assertInstanceOf(DisplayP3::class, $color);
        $this->assertSame('color(display-p3 0.9 0.9 0.97)', $color->toString());
    }

    public function testCreateFromStringHex(): void
    {
        $color = Color::createFromString('#e6e6fa');

        $this->assertInstanceOf(Rgb::class, $color);
        $this->assertSame('#e6e6fa', $color->toString());
    }

    public function testCreateFromStringHexShort(): void
    {
        $color = Color::createFromString('#f00');

        $this->assertInstanceOf(Rgb::class, $color);
        $this->assertSame('#f00', $color->toString());
    }

    public function testCreateFromStringHexShortWithAlpha(): void
    {
        $color = Color::createFromString('#f008');

        $this->assertInstanceOf(Rgb::class, $color);
        $this->assertSame('#f008', $color->toString());
    }

    public function testCreateFromStringHexWithAlpha(): void
    {
        $color = Color::createFromString('#e6e6fa80');

        $this->assertInstanceOf(Rgb::class, $color);
        $this->assertSame('#e6e6fa80', $color->toString());
    }

    public function testCreateFromStringHsl(): void
    {
        $color = Color::createFromString('hsl(240deg 66.67% 94.12%)');

        $this->assertInstanceOf(Hsl::class, $color);
        $this->assertSame('hsl(240deg 66.67% 94.12%)', $color->toString());
    }

    public function testCreateFromStringHslGrad(): void
    {
        $color = Color::createFromString('hsl(266.6667grad 66.67% 94.12%)');

        $this->assertInstanceOf(Hsl::class, $color);
        $this->assertSame('hsl(240deg 66.67% 94.12%)', $color->toString());
    }

    public function testCreateFromStringHslLegacy(): void
    {
        $color = Color::createFromString('hsl(240, 66.67%, 94.12%)');

        $this->assertInstanceOf(Hsl::class, $color);
        $this->assertSame('hsl(240deg 66.67% 94.12%)', $color->toString());
    }

    public function testCreateFromStringHslLegacyWithAlpha(): void
    {
        $color = Color::createFromString('hsla(240, 66.67%, 94.12%, 0.5)');

        $this->assertInstanceOf(Hsl::class, $color);
        $this->assertSame('hsl(240deg 66.67% 94.12% / 50%)', $color->toString());
    }

    public function testCreateFromStringHslPercent(): void
    {
        $color = Color::createFromString('hsl(66.667% 66.67% 94.12%)');

        $this->assertInstanceOf(Hsl::class, $color);
        $this->assertSame('hsl(240deg 66.67% 94.12%)', $color->toString());
    }

    public function testCreateFromStringHslPercentAlpha(): void
    {
        $color = Color::createFromString('hsl(240deg 66.67% 94.12% / 50%)');

        $this->assertInstanceOf(Hsl::class, $color);
        $this->assertSame('hsl(240deg 66.67% 94.12% / 50%)', $color->toString());
    }

    public function testCreateFromStringHslRad(): void
    {
        $color = Color::createFromString('hsl(4.18879rad 66.67% 94.12%)');

        $this->assertInstanceOf(Hsl::class, $color);
        $this->assertSame('hsl(240deg 66.67% 94.12%)', $color->toString());
    }

    public function testCreateFromStringHslTurn(): void
    {
        $color = Color::createFromString('hsl(0.66667turn 66.67% 94.12%)');

        $this->assertInstanceOf(Hsl::class, $color);
        $this->assertSame('hsl(240deg 66.67% 94.12%)', $color->toString());
    }

    public function testCreateFromStringHslWithAlpha(): void
    {
        $color = Color::createFromString('hsl(240deg 66.67% 94.12% / 50%)');

        $this->assertInstanceOf(Hsl::class, $color);
        $this->assertSame('hsl(240deg 66.67% 94.12% / 50%)', $color->toString());
    }

    public function testCreateFromStringHwb(): void
    {
        $color = Color::createFromString('hwb(240deg 90.2% 1.96%)');

        $this->assertInstanceOf(Hwb::class, $color);
        $this->assertSame('hwb(240deg 90.2% 1.96%)', $color->toString());
    }

    public function testCreateFromStringHwbLegacy(): void
    {
        $color = Color::createFromString('hwb(240, 90.2%, 1.96%)');

        $this->assertInstanceOf(Hwb::class, $color);
        $this->assertSame('hwb(240deg 90.2% 1.96%)', $color->toString());
    }

    public function testCreateFromStringHwbWithAlpha(): void
    {
        $color = Color::createFromString('hwb(240deg 90.2% 1.96% / 0.5)');

        $this->assertInstanceOf(Hwb::class, $color);
        $this->assertSame('hwb(240deg 90.2% 1.96% / 50%)', $color->toString());
    }

    public function testCreateFromStringInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Color string `invalid` is not valid.');

        Color::createFromString('invalid');
    }

    public function testCreateFromStringLab(): void
    {
        $color = Color::createFromString('lab(91.74 2.78 -9.72)');

        $this->assertInstanceOf(Lab::class, $color);
        $this->assertSame('lab(91.74% 2.78 -9.72)', $color->toString());
    }

    public function testCreateFromStringLabPercent(): void
    {
        $color = Color::createFromString('lab(91.74% 2.224% -7.776%)');

        $this->assertInstanceOf(Lab::class, $color);
        $this->assertSame('lab(91.74% 2.78 -9.72)', $color->toString());
    }

    public function testCreateFromStringLabPercentMapping(): void
    {
        $color = Color::createFromString('lab(50% 100% -100%)');

        $this->assertInstanceOf(Lab::class, $color);
        $this->assertSame('lab(50% 125 -125)', $color->toString());
    }

    public function testCreateFromStringLch(): void
    {
        $color = Color::createFromString('lch(91.74 10.11 285.93)');

        $this->assertInstanceOf(Lch::class, $color);
        $this->assertSame('lch(91.74% 10.11 285.93deg)', $color->toString());
    }

    public function testCreateFromStringLchNegativeChroma(): void
    {
        $color = Color::createFromString('lch(50% -10 120)');

        $this->assertInstanceOf(Lch::class, $color);
        $this->assertSame('lch(50% 0 120deg)', $color->toString());
    }

    public function testCreateFromStringLchPercent(): void
    {
        $color = Color::createFromString('lch(91.74% 6.74% 285.93)');

        $this->assertInstanceOf(Lch::class, $color);
        $this->assertSame('lch(91.74% 10.11 285.93deg)', $color->toString());
    }

    public function testCreateFromStringLchPercentMapping(): void
    {
        $color = Color::createFromString('lch(50% 100% 120)');

        $this->assertInstanceOf(Lch::class, $color);
        $this->assertSame('lch(50% 150 120deg)', $color->toString());
    }

    public function testCreateFromStringName(): void
    {
        $color = Color::createFromString('red');

        $this->assertInstanceOf(Rgb::class, $color);
        $this->assertSame('#f00', $color->toString());
    }

    public function testCreateFromStringOkLab(): void
    {
        $color = Color::createFromString('oklab(0.93 0.01 -0.03)');

        $this->assertInstanceOf(OkLab::class, $color);
        $this->assertSame('oklab(0.93 0.01 -0.03)', $color->toString());
    }

    public function testCreateFromStringOkLabPercent(): void
    {
        $color = Color::createFromString('oklab(93% 25% -75%)');

        $this->assertInstanceOf(OkLab::class, $color);
        $this->assertSame('oklab(0.93 0.1 -0.3)', $color->toString());
    }

    public function testCreateFromStringOkLabPercentMapping(): void
    {
        $color = Color::createFromString('oklab(50% 100% -100%)');

        $this->assertInstanceOf(OkLab::class, $color);
        $this->assertSame('oklab(0.5 0.4 -0.4)', $color->toString());
    }

    public function testCreateFromStringOkLch(): void
    {
        $color = Color::createFromString('oklch(0.93 0.03 285.8)');

        $this->assertInstanceOf(OkLch::class, $color);
        $this->assertSame('oklch(0.93 0.03 285.8deg)', $color->toString());
    }

    public function testCreateFromStringOkLchNegativeChroma(): void
    {
        $color = Color::createFromString('oklch(50% -10% 120)');

        $this->assertInstanceOf(OkLch::class, $color);
        $this->assertSame('oklch(0.5 0 120deg)', $color->toString());
    }

    public function testCreateFromStringOkLchPercent(): void
    {
        $color = Color::createFromString('oklch(93% 75% 285.8)');

        $this->assertInstanceOf(OkLch::class, $color);
        $this->assertSame('oklch(0.93 0.3 285.8deg)', $color->toString());
    }

    public function testCreateFromStringOkLchPercentMapping(): void
    {
        $color = Color::createFromString('oklch(50% 100% 120)');

        $this->assertInstanceOf(OkLch::class, $color);
        $this->assertSame('oklch(0.5 0.4 120deg)', $color->toString());
    }

    public function testCreateFromStringOutOfRangeColorFunction(): void
    {
        $color = Color::createFromString('color(srgb 1.2 0 0)');

        $this->assertInstanceOf(Srgb::class, $color);
        $this->assertSame('color(srgb 1.2 0 0)', $color->toString());
    }

    public function testCreateFromStringOutOfRangeRgb(): void
    {
        $color = Color::createFromString('rgb(300 0 0)');

        $this->assertInstanceOf(Rgb::class, $color);
        $this->assertSame('rgb(300 0 0)', $color->toString());
    }

    public function testCreateFromStringProPhotoRgb(): void
    {
        $color = Color::createFromString('color(prophoto-rgb 0.89 0.88 0.96)');

        $this->assertInstanceOf(ProPhotoRgb::class, $color);
        $this->assertSame('color(prophoto-rgb 0.89 0.88 0.96)', $color->toString());
    }

    public function testCreateFromStringProPhotoRgbPercent(): void
    {
        $color = Color::createFromString('color(prophoto-rgb 89% 88% 96%)');

        $this->assertInstanceOf(ProPhotoRgb::class, $color);
        $this->assertSame('color(prophoto-rgb 0.89 0.88 0.96)', $color->toString());
    }

    public function testCreateFromStringRec2020(): void
    {
        $color = Color::createFromString('color(rec2020 0.89 0.89 0.97)');

        $this->assertInstanceOf(Rec2020::class, $color);
        $this->assertSame('color(rec2020 0.89 0.89 0.97)', $color->toString());
    }

    public function testCreateFromStringRec2020Percent(): void
    {
        $color = Color::createFromString('color(rec2020 89% 89% 97%)');

        $this->assertInstanceOf(Rec2020::class, $color);
        $this->assertSame('color(rec2020 0.89 0.89 0.97)', $color->toString());
    }

    public function testCreateFromStringRgb(): void
    {
        $color = Color::createFromString('rgb(230 230 250)');

        $this->assertInstanceOf(Rgb::class, $color);
        $this->assertSame('rgb(230 230 250)', $color->toString());
    }

    public function testCreateFromStringRgbaLegacy(): void
    {
        $color = Color::createFromString('rgba(230, 230, 250, 0.5)');

        $this->assertInstanceOf(Rgb::class, $color);
        $this->assertSame('rgb(230 230 250 / 50%)', $color->toString());
    }

    public function testCreateFromStringRgbLegacy(): void
    {
        $color = Color::createFromString('rgb(230, 230, 250)');

        $this->assertInstanceOf(Rgb::class, $color);
        $this->assertSame('rgb(230 230 250)', $color->toString());
    }

    public function testCreateFromStringRgbWithAlpha(): void
    {
        $color = Color::createFromString('rgb(230 230 250 / 50%)');

        $this->assertInstanceOf(Rgb::class, $color);
        $this->assertSame('rgb(230 230 250 / 50%)', $color->toString());
    }

    public function testCreateFromStringSrgb(): void
    {
        $color = Color::createFromString('color(srgb 0.9 0.9 0.98)');

        $this->assertInstanceOf(Srgb::class, $color);
        $this->assertSame('color(srgb 0.9 0.9 0.98)', $color->toString());
    }

    public function testCreateFromStringSrgbLinear(): void
    {
        $color = Color::createFromString('color(srgb-linear 0.79 0.79 0.96)');

        $this->assertInstanceOf(SrgbLinear::class, $color);
        $this->assertSame('color(srgb-linear 0.79 0.79 0.96)', $color->toString());
    }

    public function testCreateFromStringSrgbLinearPercent(): void
    {
        $color = Color::createFromString('color(srgb-linear 79% 79% 96%)');

        $this->assertInstanceOf(SrgbLinear::class, $color);
        $this->assertSame('color(srgb-linear 0.79 0.79 0.96)', $color->toString());
    }

    public function testCreateFromStringSrgbPercent(): void
    {
        $color = Color::createFromString('color(srgb 90% 90% 98%)');

        $this->assertInstanceOf(Srgb::class, $color);
        $this->assertSame('color(srgb 0.9 0.9 0.98)', $color->toString());
    }

    public function testCreateFromStringTransparent(): void
    {
        $color = Color::createFromString('transparent');

        $this->assertInstanceOf(Rgb::class, $color);
        $this->assertSame('transparent', $color->toString(name: true));
    }

    public function testCreateFromStringXyz(): void
    {
        $color = Color::createFromString('color(xyz 0.78 0.8 1.02)');

        $this->assertInstanceOf(XyzD65::class, $color);
        $this->assertSame('color(xyz-d65 0.78 0.8 1.02)', $color->toString());
    }

    public function testCreateFromStringXyzD50(): void
    {
        $color = Color::createFromString('color(xyz-d50 0.79 0.8 0.77)');

        $this->assertInstanceOf(XyzD50::class, $color);
        $this->assertSame('color(xyz-d50 0.79 0.8 0.77)', $color->toString());
    }

    public function testCreateFromStringXyzD50Percent(): void
    {
        $color = Color::createFromString('color(xyz-d50 79% 80% 77%)');

        $this->assertInstanceOf(XyzD50::class, $color);
        $this->assertSame('color(xyz-d50 0.79 0.8 0.77)', $color->toString());
    }

    public function testCreateFromStringXyzD65(): void
    {
        $color = Color::createFromString('color(xyz-d65 0.78 0.8 1.02)');

        $this->assertInstanceOf(XyzD65::class, $color);
        $this->assertSame('color(xyz-d65 0.78 0.8 1.02)', $color->toString());
    }

    public function testCreateFromStringXyzD65Percent(): void
    {
        $color = Color::createFromString('color(xyz-d65 78% 80% 102%)');

        $this->assertInstanceOf(XyzD65::class, $color);
        $this->assertSame('color(xyz-d65 0.78 0.8 1.02)', $color->toString());
    }

    public function testCreateFromStringXyzPercent(): void
    {
        $color = Color::createFromString('color(xyz 78% 80% 102%)');

        $this->assertInstanceOf(XyzD65::class, $color);
        $this->assertSame('color(xyz-d65 0.78 0.8 1.02)', $color->toString());
    }

    public function testCreateFromXyzD50(): void
    {
        $color = Color::createFromXyzD50(0.79, 0.8, 0.77);

        $this->assertInstanceOf(XyzD50::class, $color);
        $this->assertSame('color(xyz-d50 0.79 0.8 0.77)', $color->toString());
    }

    public function testCreateFromXyzD65(): void
    {
        $color = Color::createFromXyzD65(0.78, 0.8, 1.02);

        $this->assertInstanceOf(XyzD65::class, $color);
        $this->assertSame('color(xyz-d65 0.78 0.8 1.02)', $color->toString());
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Color::class)
        );
    }

    public function testFitGamut(): void
    {
        $color = Color::createFromXyzD65(1, 0, 0);
        $source = $color->toSrgbLinear();
        $fitted = $color->fitGamut('srgb-linear')->toSrgbLinear();

        $this->assertLessThan(0, $source->getGreen());
        $this->assertGreaterThanOrEqual(0, $fitted->getRed());
        $this->assertLessThanOrEqual(1, $fitted->getRed());
        $this->assertGreaterThanOrEqual(0, $fitted->getGreen());
        $this->assertLessThanOrEqual(1, $fitted->getGreen());
        $this->assertGreaterThanOrEqual(0, $fitted->getBlue());
        $this->assertLessThanOrEqual(1, $fitted->getBlue());
    }

    public function testGetAlpha(): void
    {
        $color = Color::createFromString('rgb(230 230 250 / 50%)');

        $this->assertSame(0.5, $color->getAlpha());
    }

    public function testMacro(): void
    {
        $this->assertEmpty(
            array_diff([MacroTrait::class, StaticMacroTrait::class], class_uses(Color::class))
        );
    }

    public function testOkLabRoundTrip(): void
    {
        $color = Color::createFromOkLab(0.7, 0.4, 0.4);
        $result = $color->toOkLch()->toOkLab();

        $this->assertEqualsWithDelta(0.7, $result->getLightness(), 1e-12);
        $this->assertEqualsWithDelta(0.4, $result->getA(), 1e-12);
        $this->assertEqualsWithDelta(0.4, $result->getB(), 1e-12);
    }

    public function testSerializable(): void
    {
        $color = Color::createFromString('lavender');

        $this->assertSame(
            $color->toString(),
            unserialize(serialize($color))->toString()
        );
    }

    public function testToInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Color space `invalid` is not valid.');

        Color::createFromString('lavender')->to('invalid');
    }

    public function testXyzAdaptationRoundTrip(): void
    {
        $color = Color::createFromXyzD50(0, 0, 1);
        $result = $color->toXyzD65()->toXyzD50();

        $this->assertEqualsWithDelta(0, $result->getX(), 1e-6);
        $this->assertEqualsWithDelta(0, $result->getY(), 1e-6);
        $this->assertEqualsWithDelta(1, $result->getZ(), 1e-6);
    }
}

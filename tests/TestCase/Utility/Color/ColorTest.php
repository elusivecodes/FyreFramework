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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function class_uses;
use function serialize;
use function sprintf;
use function unserialize;

/**
 * @phpstan-type FactoryMethod 'createFromA98Rgb'|'createFromDisplayP3'|'createFromDisplayP3Linear'|'createFromHsl'|'createFromHwb'|'createFromLab'|'createFromLch'|'createFromOkLab'|'createFromOkLch'|'createFromProPhotoRgb'|'createFromRec2020'|'createFromRgb'|'createFromSrgb'|'createFromSrgbLinear'|'createFromXyzD50'|'createFromXyzD65'
 */
final class ColorTest extends TestCase
{
    /**
     * @return array<string, array{string, (float|int)[], class-string<Color>, string}>
     */
    public static function factoryProvider(): array
    {
        return [
            'a 98 rgb' => ['createFromA98Rgb', [0.9, 0.9, 0.98], A98Rgb::class, 'color(a98-rgb 0.9 0.9 0.98)'],
            'display p 3' => ['createFromDisplayP3', [0.9, 0.9, 0.97], DisplayP3::class, 'color(display-p3 0.9 0.9 0.97)'],
            'display p 3 linear' => ['createFromDisplayP3Linear', [0.79, 0.79, 0.94], DisplayP3Linear::class, 'color(display-p3-linear 0.79 0.79 0.94)'],
            'hsl' => ['createFromHsl', [240, 66.67, 94.12], Hsl::class, 'hsl(240deg 66.67% 94.12%)'],
            'hwb' => ['createFromHwb', [120, 90.2, 1.96], Hwb::class, 'hwb(120deg 90.2% 1.96%)'],
            'lab' => ['createFromLab', [91.74, 2.78, -9.72], Lab::class, 'lab(91.74% 2.78 -9.72)'],
            'lch' => ['createFromLch', [91.74, 10.11, 285.93], Lch::class, 'lch(91.74% 10.11 285.93deg)'],
            'ok lab' => ['createFromOkLab', [0.93, 0.01, -0.03], OkLab::class, 'oklab(0.93 0.01 -0.03)'],
            'ok lch' => ['createFromOkLch', [0.93, 0.03, 285.8], OkLch::class, 'oklch(0.93 0.03 285.8deg)'],
            'pro photo rgb' => ['createFromProPhotoRgb', [0.89, 0.88, 0.96], ProPhotoRgb::class, 'color(prophoto-rgb 0.89 0.88 0.96)'],
            'rec 2020' => ['createFromRec2020', [0.89, 0.89, 0.97], Rec2020::class, 'color(rec2020 0.89 0.89 0.97)'],
            'rgb' => ['createFromRgb', [230, 230, 250], Rgb::class, 'rgb(230 230 250)'],
            'srgb' => ['createFromSrgb', [0.9, 0.9, 0.98], Srgb::class, 'color(srgb 0.9 0.9 0.98)'],
            'srgb linear' => ['createFromSrgbLinear', [0.79, 0.79, 0.96], SrgbLinear::class, 'color(srgb-linear 0.79 0.79 0.96)'],
            'xyz d 50' => ['createFromXyzD50', [0.79, 0.8, 0.77], XyzD50::class, 'color(xyz-d50 0.79 0.8 0.77)'],
            'xyz d 65' => ['createFromXyzD65', [0.78, 0.8, 1.02], XyzD65::class, 'color(xyz-d65 0.78 0.8 1.02)'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidStringProvider(): array
    {
        return [
            'invalid' => ['invalid'],
            'hex length' => ['#12345'],
            'hex length with alpha' => ['#1234567'],
        ];
    }

    /**
     * @return array<string, array{string, class-string<Color>, string}>
     */
    public static function stringProvider(): array
    {
        return [
            'a 98 rgb' => ['color(a98-rgb 0.9 0.9 0.98)', A98Rgb::class, 'color(a98-rgb 0.9 0.9 0.98)'],
            'a 98 rgb percent' => ['color(a98-rgb 90% 90% 98%)', A98Rgb::class, 'color(a98-rgb 0.9 0.9 0.98)'],
            'color percent mapping' => ['color(display-p3 25% 50% 75%)', DisplayP3::class, 'color(display-p3 0.25 0.5 0.75)'],
            'display p 3' => ['color(display-p3 0.9 0.9 0.97)', DisplayP3::class, 'color(display-p3 0.9 0.9 0.97)'],
            'display p 3 linear' => ['color(display-p3-linear 0.79 0.79 0.94)', DisplayP3Linear::class, 'color(display-p3-linear 0.79 0.79 0.94)'],
            'display p 3 linear percent' => ['color(display-p3-linear 79% 79% 94%)', DisplayP3Linear::class, 'color(display-p3-linear 0.79 0.79 0.94)'],
            'display p 3 percent' => ['color(display-p3 90% 90% 97%)', DisplayP3::class, 'color(display-p3 0.9 0.9 0.97)'],
            'hex' => ['#e6e6fa', Rgb::class, '#e6e6fa'],
            'hex short' => ['#f00', Rgb::class, '#f00'],
            'hex short with alpha' => ['#f008', Rgb::class, '#f008'],
            'hex with alpha' => ['#e6e6fa80', Rgb::class, '#e6e6fa80'],
            'hsl' => ['hsl(240deg 66.67% 94.12%)', Hsl::class, 'hsl(240deg 66.67% 94.12%)'],
            'hsl grad' => ['hsl(266.6667grad 66.67% 94.12%)', Hsl::class, 'hsl(240deg 66.67% 94.12%)'],
            'hsl legacy' => ['hsl(240, 66.67%, 94.12%)', Hsl::class, 'hsl(240deg 66.67% 94.12%)'],
            'hsl legacy with alpha' => ['hsla(240, 66.67%, 94.12%, 0.5)', Hsl::class, 'hsl(240deg 66.67% 94.12% / 50%)'],
            'hsl percent' => ['hsl(66.667% 66.67% 94.12%)', Hsl::class, 'hsl(240deg 66.67% 94.12%)'],
            'hsl percent alpha' => ['hsl(240deg 66.67% 94.12% / 50%)', Hsl::class, 'hsl(240deg 66.67% 94.12% / 50%)'],
            'hsl rad' => ['hsl(4.18879rad 66.67% 94.12%)', Hsl::class, 'hsl(240deg 66.67% 94.12%)'],
            'hsl turn' => ['hsl(0.66667turn 66.67% 94.12%)', Hsl::class, 'hsl(240deg 66.67% 94.12%)'],
            'hsl with alpha' => ['hsl(240deg 66.67% 94.12% / 50%)', Hsl::class, 'hsl(240deg 66.67% 94.12% / 50%)'],
            'hwb' => ['hwb(240deg 90.2% 1.96%)', Hwb::class, 'hwb(240deg 90.2% 1.96%)'],
            'hwb legacy' => ['hwb(240, 90.2%, 1.96%)', Hwb::class, 'hwb(240deg 90.2% 1.96%)'],
            'hwb with alpha' => ['hwb(240deg 90.2% 1.96% / 0.5)', Hwb::class, 'hwb(240deg 90.2% 1.96% / 50%)'],
            'lab' => ['lab(91.74 2.78 -9.72)', Lab::class, 'lab(91.74% 2.78 -9.72)'],
            'lab percent' => ['lab(91.74% 2.224% -7.776%)', Lab::class, 'lab(91.74% 2.78 -9.72)'],
            'lab percent mapping' => ['lab(50% 100% -100%)', Lab::class, 'lab(50% 125 -125)'],
            'lch' => ['lch(91.74 10.11 285.93)', Lch::class, 'lch(91.74% 10.11 285.93deg)'],
            'lch negative chroma' => ['lch(50% -10 120)', Lch::class, 'lch(50% 0 120deg)'],
            'lch percent' => ['lch(91.74% 6.74% 285.93)', Lch::class, 'lch(91.74% 10.11 285.93deg)'],
            'lch percent mapping' => ['lch(50% 100% 120)', Lch::class, 'lch(50% 150 120deg)'],
            'name' => ['red', Rgb::class, '#f00'],
            'ok lab' => ['oklab(0.93 0.01 -0.03)', OkLab::class, 'oklab(0.93 0.01 -0.03)'],
            'ok lab percent' => ['oklab(93% 25% -75%)', OkLab::class, 'oklab(0.93 0.1 -0.3)'],
            'ok lab percent mapping' => ['oklab(50% 100% -100%)', OkLab::class, 'oklab(0.5 0.4 -0.4)'],
            'ok lch' => ['oklch(0.93 0.03 285.8)', OkLch::class, 'oklch(0.93 0.03 285.8deg)'],
            'ok lch negative chroma' => ['oklch(50% -10% 120)', OkLch::class, 'oklch(0.5 0 120deg)'],
            'ok lch percent' => ['oklch(93% 75% 285.8)', OkLch::class, 'oklch(0.93 0.3 285.8deg)'],
            'ok lch percent mapping' => ['oklch(50% 100% 120)', OkLch::class, 'oklch(0.5 0.4 120deg)'],
            'out of range color function' => ['color(srgb 1.2 0 0)', Srgb::class, 'color(srgb 1.2 0 0)'],
            'out of range rgb' => ['rgb(300 0 0)', Rgb::class, 'rgb(300 0 0)'],
            'pro photo rgb' => ['color(prophoto-rgb 0.89 0.88 0.96)', ProPhotoRgb::class, 'color(prophoto-rgb 0.89 0.88 0.96)'],
            'pro photo rgb percent' => ['color(prophoto-rgb 89% 88% 96%)', ProPhotoRgb::class, 'color(prophoto-rgb 0.89 0.88 0.96)'],
            'rec 2020' => ['color(rec2020 0.89 0.89 0.97)', Rec2020::class, 'color(rec2020 0.89 0.89 0.97)'],
            'rec 2020 percent' => ['color(rec2020 89% 89% 97%)', Rec2020::class, 'color(rec2020 0.89 0.89 0.97)'],
            'rgb' => ['rgb(230 230 250)', Rgb::class, 'rgb(230 230 250)'],
            'rgba legacy' => ['rgba(230, 230, 250, 0.5)', Rgb::class, 'rgb(230 230 250 / 50%)'],
            'rgb legacy' => ['rgb(230, 230, 250)', Rgb::class, 'rgb(230 230 250)'],
            'rgb with alpha' => ['rgb(230 230 250 / 50%)', Rgb::class, 'rgb(230 230 250 / 50%)'],
            'srgb' => ['color(srgb 0.9 0.9 0.98)', Srgb::class, 'color(srgb 0.9 0.9 0.98)'],
            'srgb linear' => ['color(srgb-linear 0.79 0.79 0.96)', SrgbLinear::class, 'color(srgb-linear 0.79 0.79 0.96)'],
            'srgb linear percent' => ['color(srgb-linear 79% 79% 96%)', SrgbLinear::class, 'color(srgb-linear 0.79 0.79 0.96)'],
            'srgb percent' => ['color(srgb 90% 90% 98%)', Srgb::class, 'color(srgb 0.9 0.9 0.98)'],
            'xyz' => ['color(xyz 0.78 0.8 1.02)', XyzD65::class, 'color(xyz-d65 0.78 0.8 1.02)'],
            'xyz d 50' => ['color(xyz-d50 0.79 0.8 0.77)', XyzD50::class, 'color(xyz-d50 0.79 0.8 0.77)'],
            'xyz d 50 percent' => ['color(xyz-d50 79% 80% 77%)', XyzD50::class, 'color(xyz-d50 0.79 0.8 0.77)'],
            'xyz d 65' => ['color(xyz-d65 0.78 0.8 1.02)', XyzD65::class, 'color(xyz-d65 0.78 0.8 1.02)'],
            'xyz d 65 percent' => ['color(xyz-d65 78% 80% 102%)', XyzD65::class, 'color(xyz-d65 0.78 0.8 1.02)'],
            'xyz percent' => ['color(xyz 78% 80% 102%)', XyzD65::class, 'color(xyz-d65 0.78 0.8 1.02)'],
        ];
    }

    /**
     * @param FactoryMethod $method
     * @param (float|int)[] $arguments
     * @param class-string<Color> $expectedClass
     */
    #[DataProvider('factoryProvider')]
    public function testCreateFromFactory(
        string $method,
        array $arguments,
        string $expectedClass,
        string $expected
    ): void {
        $color = Color::$method(...$arguments);

        $this->assertInstanceOf($expectedClass, $color);
        $this->assertSame($expected, $color->toString());
    }

    /**
     * @param class-string<Color> $expectedClass
     */
    #[DataProvider('stringProvider')]
    public function testCreateFromString(string $value, string $expectedClass, string $expected): void
    {
        $color = Color::createFromString($value);

        $this->assertInstanceOf($expectedClass, $color);
        $this->assertSame($expected, $color->toString());
    }

    #[DataProvider('invalidStringProvider')]
    public function testCreateFromStringInvalid(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Color string `%s` is not valid.',
            $value
        ));

        Color::createFromString($value);
    }

    public function testCreateFromStringTransparent(): void
    {
        $color = Color::createFromString('transparent');

        $this->assertInstanceOf(Rgb::class, $color);
        $this->assertSame('transparent', $color->toString(name: true));
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

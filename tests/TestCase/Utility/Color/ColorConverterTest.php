<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Color;

use Fyre\Utility\Color\ColorConverter;
use PHPUnit\Framework\TestCase;

final class ColorConverterTest extends TestCase
{
    public function testA98RgbToXyzD65(): void
    {
        $this->assertArraysAreIdentical(
            [0.16773166428519598, 0.19070368921307673, 0.5432420115093654],
            ColorConverter::a98RgbToXyzD65(0.25, 0.5, 0.75)
        );
    }

    public function testDisplayP3LinearToDisplayP3(): void
    {
        $this->assertArraysAreIdentical(
            [-0.5, 0.02, 0.5],
            ColorConverter::displayP3LinearToDisplayP3(
                -0.21404114048223255,
                0.0015479876160990713,
                0.21404114048223255
            )
        );
    }

    public function testDisplayP3LinearToXyzD65(): void
    {
        $this->assertArraysAreIdentical(
            [0.4031395476723724, 0.46257808750599905, 0.8055149676051832],
            ColorConverter::displayP3LinearToXyzD65(0.25, 0.5, 0.75)
        );
    }

    public function testDisplayP3ToDisplayP3Linear(): void
    {
        $this->assertArraysAreIdentical(
            [-0.21404114048223255, 0.0015479876160990713, 0.21404114048223255],
            ColorConverter::displayP3ToDisplayP3Linear(-0.5, 0.02, 0.5)
        );
    }

    public function testLabToXyzD50(): void
    {
        $this->assertArraysAreIdentical(
            [0.35273006792414424, 0.2812333429004879, 0.5160241871850108],
            ColorConverter::labToXyzD50(60, 30, -40)
        );
    }

    public function testOkLabToXyzD65(): void
    {
        $this->assertArraysAreIdentical(
            [0.4384240326511916, 0.3158934720492758, 0.9281550660709309],
            ColorConverter::okLabToXyzD65(0.7, 0.1, -0.15)
        );
    }

    public function testProPhotoRgbToXyzD50(): void
    {
        $this->assertArraysAreIdentical(
            [0.0029292330170698558, 0.12096200204602699, 0.5521676847424244],
            ColorConverter::prophotoRgbToXyzD50(-0.2, 0.4, 0.8)
        );
    }

    public function testRec2020ToXyzD65(): void
    {
        $this->assertArraysAreIdentical(
            [0.10150912714954471, 0.10438451082015363, 0.6241614492327773],
            ColorConverter::rec2020ToXyzD65(-0.2, 0.4, 0.8)
        );
    }

    public function testSrgbLinearToSrgb(): void
    {
        $this->assertArraysAreIdentical(
            [-0.5, 0.02, 0.5],
            ColorConverter::srgbLinearToSrgb(
                -0.21404114048223255,
                0.0015479876160990713,
                0.21404114048223255
            )
        );
    }

    public function testSrgbLinearToXyzD65(): void
    {
        $this->assertArraysAreIdentical(
            [0.4172504608098046, 0.46488832737230584, 0.7773292087634563],
            ColorConverter::srgbLinearToXyzD65(0.25, 0.5, 0.75)
        );
    }

    public function testSrgbToSrgbLinear(): void
    {
        $this->assertArraysAreIdentical(
            [-0.21404114048223255, 0.0015479876160990713, 0.21404114048223255],
            ColorConverter::srgbToSrgbLinear(-0.5, 0.02, 0.5)
        );
    }

    public function testXyzD50ToLab(): void
    {
        $this->assertArraysAreIdentical(
            [69.46953076845696, -29.605530973801365, 22.660148493094923],
            ColorConverter::xyzD50ToLab(0.3, 0.4, 0.2)
        );
    }

    public function testXyzD50ToProPhotoRgb(): void
    {
        $this->assertArraysAreIdentical(
            [-0.04271290611735369, 0.8226934389233042, 0.4550584827304945],
            ColorConverter::xyzD50ToProPhotoRgb(0.1, 0.5, 0.2)
        );
    }

    public function testXyzD50ToXyzD65(): void
    {
        $this->assertArraysAreIdentical(
            [0.2747635602980644, 0.513686352601124, 0.990599123748264],
            ColorConverter::xyzD50ToXyzD65(0.25, 0.5, 0.75)
        );
    }

    public function testXyzD65ToA98Rgb(): void
    {
        $this->assertArraysAreIdentical(
            [-0.20502264048390156, 0.8649580330178266, 0.8533509021783342],
            ColorConverter::xyzD65ToA98Rgb(0.25, 0.5, 0.75)
        );
    }

    public function testXyzD65ToDisplayP3Linear(): void
    {
        $this->assertArraysAreIdentical(
            [-0.14435066931224327, 0.6916783021502373, 0.6885386559326907],
            ColorConverter::xyzD65ToDisplayP3Linear(0.25, 0.5, 0.75)
        );
    }

    public function testXyzD65ToOkLab(): void
    {
        $this->assertArraysAreIdentical(
            [0.7655731400826722, -0.2356574530804355, -0.05089608189802286],
            ColorConverter::xyzD65ToOkLab(0.25, 0.5, 0.75)
        );
    }

    public function testXyzD65ToRec2020(): void
    {
        $this->assertArraysAreIdentical(
            [-0.3027729678685117, 0.8844335097729922, 0.47650899606819697],
            ColorConverter::xyzD65ToRec2020(0.1, 0.5, 0.2)
        );
    }

    public function testXyzD65ToSrgbLinear(): void
    {
        $this->assertArraysAreIdentical(
            [-0.3324071735286689, 0.7268391347390221, 0.7046476761619191],
            ColorConverter::xyzD65ToSrgbLinear(0.25, 0.5, 0.75)
        );
    }

    public function testXyzD65ToXyzD50(): void
    {
        $this->assertArraysAreIdentical(
            [0.23581168372015016, 0.4898188162718899, 0.5691225466547007],
            ColorConverter::xyzD65ToXyzD50(0.25, 0.5, 0.75)
        );
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Core\Traits\StaticMacroTrait;
use Fyre\Utility\Math;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class MathTest extends TestCase
{
    use AbsTestTrait;
    use AcoshTestTrait;
    use AcosTestTrait;
    use AsinhTestTrait;
    use AsinTestTrait;
    use Atan2TestTrait;
    use AtanhTestTrait;
    use AtanTestTrait;
    use BinaryToDecimalTestTrait;
    use CeilTestTrait;
    use ClampPercentTestTrait;
    use ClampTestTrait;
    use ConvertBaseTestTrait;
    use CoshTestTrait;
    use CosTestTrait;
    use DecimalToBinaryTestTrait;
    use DecimalToHexTestTrait;
    use DecimalToOctalTestTrait;
    use DegreesToRadiansTestTrait;
    use DistTestTrait;
    use ExpMinus1TestTrait;
    use ExpTestTrait;
    use FloorTestTrait;
    use FmodTestTrait;
    use HexToDecimalTestTrait;
    use HypotTestTrait;
    use InverseLerpTestTrait;
    use IsNumericTestTrait;
    use LerpTestTrait;
    use Log10TestTrait;
    use LogPlus1TestTrait;
    use LogTestTrait;
    use MapTestTrait;
    use MaxTestTrait;
    use MinTestTrait;
    use OctalToDecimalTestTrait;
    use PowTestTrait;
    use ProductTestTrait;
    use RadiansToDegreesTestTrait;
    use RandomIntTestTrait;
    use RandomTestTrait;
    use RoundTestTrait;
    use SinhTestTrait;
    use SinTestTrait;
    use SqrtTestTrait;
    use SumTestTrait;
    use TanhTestTrait;
    use TanTestTrait;
    use ToStepTestTrait;

    public function testMacro(): void
    {
        $this->assertContains(
            StaticMacroTrait::class,
            class_uses(Math::class)
        );
    }
}

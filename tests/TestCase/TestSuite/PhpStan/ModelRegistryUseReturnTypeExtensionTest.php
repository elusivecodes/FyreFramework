<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\PhpStan;

use PHPStan\Testing\TypeInferenceTestCase;

use const ROOT;

final class ModelRegistryUseReturnTypeExtensionTest extends TypeInferenceTestCase
{
    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [ROOT.'/tests/Mock/PhpStan/phpstan.neon'];
    }

    public function testReturnTypes(): void
    {
        // Data providers run before PHPUnit starts coverage.
        $assertions = self::gatherAssertTypes(ROOT.'/tests/Mock/PhpStan/ModelRegistryUse.php');

        foreach ($assertions as $assertion) {
            $this->assertFileAsserts(...$assertion);
        }
    }
}

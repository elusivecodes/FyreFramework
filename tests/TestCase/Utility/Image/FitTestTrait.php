<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

trait FitTestTrait
{
    /**
     * @return array<string, array{int, int, int, int, int, int}>
     */
    public static function fitProvider(): array
    {
        return [
            'landscape' => [4, 2, 2, 2, 2, 1],
            'portrait' => [2, 4, 2, 2, 1, 2],
            'same size' => [2, 2, 2, 2, 2, 2],
            'upscale' => [2, 2, 4, 4, 4, 4],
        ];
    }

    #[DataProvider('fitProvider')]
    public function testFit(
        int $sourceWidth,
        int $sourceHeight,
        int $width,
        int $height,
        int $expectedWidth,
        int $expectedHeight
    ): void {
        $image1 = $this->createImage($sourceWidth, $sourceHeight);
        $image2 = $image1->fit($width, $height);

        $this->assertSame($image1, $image2);
        $this->assertSame($expectedWidth, $image2->getWidth());
        $this->assertSame($expectedHeight, $image2->getHeight());
    }

    public function testFitInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Image dimensions must be greater than 0.');

        $image = $this->createImage();
        $image->fit(0, 1);
    }
}

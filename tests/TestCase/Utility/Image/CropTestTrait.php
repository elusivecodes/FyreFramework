<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

trait CropTestTrait
{
    /**
     * @return array<string, array{int, int, int, int, string}>
     */
    public static function invalidCropProvider(): array
    {
        return [
            'height' => [0, 0, 1, 0, 'Image dimensions must be greater than 0.'],
            'horizontal offset' => [-1, 0, 1, 1, 'Crop area must be within the image bounds.'],
            'horizontal overflow' => [1, 0, 2, 1, 'Crop area must be within the image bounds.'],
            'vertical offset' => [0, -1, 1, 1, 'Crop area must be within the image bounds.'],
            'vertical overflow' => [0, 1, 1, 2, 'Crop area must be within the image bounds.'],
            'width' => [0, 0, 0, 1, 'Image dimensions must be greater than 0.'],
        ];
    }

    public function testCrop(): void
    {
        $image1 = $this->createImage();
        $image2 = $image1->crop(1, 0, 1, 2);

        $this->assertSame($image1, $image2);
        $this->assertSame(1, $image2->getWidth());
        $this->assertSame(2, $image2->getHeight());
        $this->assertArraysAreIdentical([0, 255, 0, 0], $this->getPixel($image2, 0, 0));
        $this->assertArraysAreIdentical([255, 255, 0, 0], $this->getPixel($image2, 0, 1));
    }

    #[DataProvider('invalidCropProvider')]
    public function testCropInvalid(int $x, int $y, int $width, int $height, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs($message);

        $image = $this->createImage();
        $image->crop($x, $y, $width, $height);
    }
}

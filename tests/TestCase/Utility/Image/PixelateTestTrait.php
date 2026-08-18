<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use InvalidArgumentException;

trait PixelateTestTrait
{
    public function testPixelate(): void
    {
        $image1 = $this->createImage();
        $image2 = $image1->pixelate(2);
        $pixel = $this->getPixel($image2, 0, 0);

        $this->assertSame($image1, $image2);
        $this->assertArraysAreIdentical($pixel, $this->getPixel($image2, 1, 1));
    }

    public function testPixelateInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Pixel size must be greater than 0.');

        $image = $this->createImage();
        $image->pixelate(0);
    }
}

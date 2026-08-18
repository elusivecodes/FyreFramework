<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use InvalidArgumentException;

trait BrightnessTestTrait
{
    public function testBrightness(): void
    {
        $image1 = $this->createImage();
        $image2 = $image1->brightness(20);

        $this->assertSame($image1, $image2);
        $this->assertArraysAreIdentical([255, 20, 20, 0], $this->getPixel($image2, 0, 0));
    }

    public function testBrightnessInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Brightness must be between -255 and 255.');

        $image = $this->createImage();
        $image->brightness(256);
    }
}

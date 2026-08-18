<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

trait FlipVerticalTestTrait
{
    public function testFlipVertical(): void
    {
        $image1 = $this->createImage();
        $image2 = $image1->flipVertical();

        $this->assertSame($image1, $image2);
        $this->assertArraysAreIdentical([0, 0, 255, 0], $this->getPixel($image2, 0, 0));
        $this->assertArraysAreIdentical([255, 255, 0, 0], $this->getPixel($image2, 1, 0));
        $this->assertArraysAreIdentical([255, 0, 0, 0], $this->getPixel($image2, 0, 1));
        $this->assertArraysAreIdentical([0, 255, 0, 0], $this->getPixel($image2, 1, 1));
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

trait GrayscaleTestTrait
{
    public function testGrayscale(): void
    {
        $image1 = $this->createImage();
        $image2 = $image1->grayscale();
        $color = $this->getPixel($image2, 0, 0);

        $this->assertSame($image1, $image2);
        $this->assertSame($color[0], $color[1]);
        $this->assertSame($color[1], $color[2]);
    }
}

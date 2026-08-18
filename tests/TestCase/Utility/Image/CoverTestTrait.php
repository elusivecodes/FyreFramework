<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use InvalidArgumentException;

trait CoverTestTrait
{
    public function testCover(): void
    {
        $image1 = $this->createImage(4, 2);
        $image2 = $image1->cover(2, 2);

        $this->assertSame($image1, $image2);
        $this->assertSame(2, $image2->getWidth());
        $this->assertSame(2, $image2->getHeight());
        $this->assertArraysAreIdentical([255, 0, 0, 0], $this->getPixel($image2, 0, 0));
        $this->assertArraysAreIdentical([0, 255, 0, 0], $this->getPixel($image2, 1, 0));
        $this->assertArraysAreIdentical([0, 0, 255, 0], $this->getPixel($image2, 0, 1));
        $this->assertArraysAreIdentical([255, 255, 0, 0], $this->getPixel($image2, 1, 1));
    }

    public function testCoverInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Image dimensions must be greater than 0.');

        $image = $this->createImage();
        $image->cover(0, 1);
    }

    public function testCoverSameSize(): void
    {
        $image1 = $this->createImage();
        $data = $image1->toBinary();
        $image2 = $image1->cover(2, 2);

        $this->assertSame($image1, $image2);
        $this->assertSame($data, $image2->toBinary());
    }
}

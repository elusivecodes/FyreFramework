<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use Fyre\Utility\Color\Color;
use InvalidArgumentException;

trait ContainTestTrait
{
    public function testContain(): void
    {
        $image1 = $this->createImage(4, 2);
        $image2 = $image1->contain(4, 4);

        $this->assertSame($image1, $image2);
        $this->assertSame(4, $image2->getWidth());
        $this->assertSame(4, $image2->getHeight());
        $this->assertArraysAreIdentical([0, 0, 0, 127], $this->getPixel($image2, 0, 0));
        $this->assertArraysAreIdentical([255, 0, 0, 0], $this->getPixel($image2, 0, 1));
    }

    public function testContainBackground(): void
    {
        $image1 = $this->createImage(4, 2);
        $background = Color::createFromRgb(12, 34, 56, 0.5);
        $image2 = $image1->contain(4, 4, $background);

        $this->assertSame($image1, $image2);
        $this->assertArraysAreIdentical([12, 34, 56, 64], $this->getPixel($image2, 0, 0));
    }

    public function testContainInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Image dimensions must be greater than 0.');

        $image = $this->createImage();
        $image->contain(0, 1);
    }

    public function testContainSameSize(): void
    {
        $image1 = $this->createImage();
        $data = $image1->toBinary();
        $image2 = $image1->contain(2, 2);

        $this->assertSame($image1, $image2);
        $this->assertSame($data, $image2->toBinary());
    }
}

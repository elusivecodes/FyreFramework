<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use InvalidArgumentException;

trait ResizeTestTrait
{
    public function testResize(): void
    {
        $image1 = $this->createImage(4, 2);
        $image2 = $image1->resize(2, 1);

        $this->assertSame($image1, $image2);
        $this->assertSame(2, $image2->getWidth());
        $this->assertSame(1, $image2->getHeight());
    }

    public function testResizeInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Image dimensions must be greater than 0.');

        $image = $this->createImage();
        $image->resize(0, 1);
    }

    public function testResizeSameSize(): void
    {
        $image1 = $this->createImage();
        $data = $image1->toBinary();
        $image2 = $image1->resize(2, 2);

        $this->assertSame($image1, $image2);
        $this->assertSame($data, $image2->toBinary());
    }
}

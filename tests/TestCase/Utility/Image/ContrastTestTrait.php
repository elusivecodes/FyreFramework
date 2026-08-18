<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use InvalidArgumentException;

trait ContrastTestTrait
{
    public function testContrast(): void
    {
        $image1 = $this->createImage();
        $image2 = $image1->contrast(20);

        $this->assertSame($image1, $image2);
        $this->assertSame(2, $image2->getWidth());
        $this->assertSame(2, $image2->getHeight());
    }

    public function testContrastInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Contrast must be between -100 and 100.');

        $image = $this->createImage();
        $image->contrast(101);
    }
}

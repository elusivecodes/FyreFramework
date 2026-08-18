<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use InvalidArgumentException;

trait BlurTestTrait
{
    public function testBlur(): void
    {
        $image1 = $this->createImage();
        $data = $image1->toBinary();
        $image2 = $image1->blur();

        $this->assertSame($image1, $image2);
        $this->assertNotSame($data, $image2->toBinary());
    }

    public function testBlurInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Blur passes must be greater than 0.');

        $image = $this->createImage();
        $image->blur(0);
    }
}

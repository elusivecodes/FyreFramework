<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use Fyre\Utility\Image;
use InvalidArgumentException;

trait CreateFromStringTestTrait
{
    public function testCreateFromString(): void
    {
        $image = $this->createImageData(4, 2) |> Image::createFromString(...);

        $this->assertSame(4, $image->getWidth());
        $this->assertSame(2, $image->getHeight());
    }

    public function testCreateFromStringInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Image data is not valid.');

        Image::createFromString('invalid');
    }
}

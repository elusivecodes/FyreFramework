<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use Fyre\Utility\Image;
use InvalidArgumentException;
use RuntimeException;

use function file_put_contents;

trait CreateFromFileTestTrait
{
    public function testCreateFromFile(): void
    {
        file_put_contents('tmp/test.png', $this->createImageData(4, 2));

        $image = Image::createFromFile('tmp/test.png');

        $this->assertSame(4, $image->getWidth());
        $this->assertSame(2, $image->getHeight());
    }

    public function testCreateFromFileInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Image data is not valid.');

        file_put_contents('tmp/test.png', 'invalid');
        Image::createFromFile('tmp/test.png');
    }

    public function testCreateFromFileNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Image file `tmp/test.png` could not be read.');

        Image::createFromFile('tmp/test.png');
    }
}

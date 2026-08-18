<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use InvalidArgumentException;
use RuntimeException;

use function getimagesize;

use const IMAGETYPE_PNG;

trait SaveTestTrait
{
    public function testSave(): void
    {
        $image = $this->createImage();

        $image->save('tmp/test.png');

        $data = getimagesize('tmp/test.png');

        $this->assertSame(IMAGETYPE_PNG, $data[2] ?? null);
    }

    public function testSaveEmptyFilePath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Image file path is required.');

        $image = $this->createImage();
        $image->save('');
    }

    public function testSaveExisting(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('File `tmp/test.png` already exists.');

        $image = $this->createImage();
        $image->save('tmp/test.png');
        $image->save('tmp/test.png');
    }

    public function testSaveFailed(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Image file `tmp/missing/test.png` could not be saved.');

        $image = $this->createImage();
        $image->save('tmp/missing/test.png');
    }

    public function testSaveInvalidExtension(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Image format `bmp` is not supported.');

        $image = $this->createImage();
        $image->save('tmp/test.bmp');
    }

    public function testSaveInvalidQuality(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Image quality must be between 0 and 100.');

        $image = $this->createImage();
        $image->save('tmp/test.png', 101);
    }

    public function testSaveMissingExtension(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Image file extension is required.');

        $image = $this->createImage();
        $image->save('tmp/test');
    }

    public function testSaveOverwrite(): void
    {
        $image = $this->createImage();
        $image->save('tmp/test.png');
        $image->save('tmp/test.png', overwrite: true);

        $data = getimagesize('tmp/test.png');

        $this->assertSame(IMAGETYPE_PNG, $data[2] ?? null);
    }
}

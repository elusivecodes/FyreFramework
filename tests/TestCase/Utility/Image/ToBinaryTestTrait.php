<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use Fyre\Utility\Image;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

use function function_exists;
use function getimagesizefromstring;

use const IMAGETYPE_AVIF;
use const IMAGETYPE_GIF;
use const IMAGETYPE_JPEG;
use const IMAGETYPE_PNG;
use const IMAGETYPE_WEBP;

trait ToBinaryTestTrait
{
    /**
     * @return array<string, array{string, string, int}>
     */
    public static function imageFormatProvider(): array
    {
        return [
            'avif' => ['avif', 'imageavif', IMAGETYPE_AVIF],
            'gif' => ['gif', 'imagegif', IMAGETYPE_GIF],
            'jpeg' => ['jpeg', 'imagejpeg', IMAGETYPE_JPEG],
            'jpg alias' => ['jpg', 'imagejpeg', IMAGETYPE_JPEG],
            'png' => ['png', 'imagepng', IMAGETYPE_PNG],
            'uppercase' => ['PNG', 'imagepng', IMAGETYPE_PNG],
            'webp' => ['webp', 'imagewebp', IMAGETYPE_WEBP],
        ];
    }

    /**
     * @return array<string, array{int}>
     */
    public static function imageQualityProvider(): array
    {
        return [
            'maximum' => [100],
            'minimum' => [0],
        ];
    }

    public function testToBinary(): void
    {
        $image = $this->createImage();
        $data = $image->toBinary();
        $imageData = getimagesizefromstring($data);

        $this->assertSame(IMAGETYPE_PNG, $imageData[2] ?? null);
    }

    #[DataProvider('imageFormatProvider')]
    public function testToBinaryFormat(string $format, string $function, int $expected): void
    {
        if (!function_exists($function)) {
            $this->markTestSkipped('The '.$function.' function is not available.');
        }

        $image = $this->createImage();
        $data = $image->toBinary($format);
        $imageData = getimagesizefromstring($data);

        $this->assertSame($expected, $imageData[2] ?? null);
    }

    public function testToBinaryInvalidFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Image format `bmp` is not supported.');

        $image = $this->createImage();
        $image->toBinary('bmp');
    }

    public function testToBinaryInvalidQuality(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Image quality must be between 0 and 100.');

        $image = $this->createImage();
        $image->toBinary('png', 101);
    }

    public function testToBinaryJpegTransparency(): void
    {
        $image1 = $this->createImage(4, 2);
        $image2 = $image1->contain(4, 4);

        $this->assertSame($image1, $image2);
        $this->assertArraysAreIdentical([0, 0, 0, 127], $this->getPixel($image2, 0, 0));

        $data = $image2->toBinary('jpeg', 100);
        $image3 = Image::createFromString($data);

        $this->assertArraysAreIdentical([255, 255, 255, 0], $this->getPixel($image3, 0, 0));
        $this->assertArraysAreIdentical([0, 0, 0, 127], $this->getPixel($image2, 0, 0));
    }

    #[DataProvider('imageQualityProvider')]
    public function testToBinaryQuality(int $quality): void
    {
        $image = $this->createImage();
        $data = $image->toBinary('png', $quality);
        $imageData = getimagesizefromstring($data);

        $this->assertSame(IMAGETYPE_PNG, $imageData[2] ?? null);
    }
}

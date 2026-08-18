<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use Fyre\Utility\Image;
use GdImage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

use function file_put_contents;
use function imagecreatefromstring;
use function imagejpeg;
use function ob_get_clean;
use function ob_start;
use function pack;
use function strlen;
use function substr;

trait OrientTestTrait
{
    /**
     * @return array<string, array{
     *     int,
     *     int,
     *     int,
     *     array{
     *         array{int, int, int, int},
     *         array{int, int, int, int},
     *         array{int, int, int, int},
     *         array{int, int, int, int}
     *     }
     * }>
     */
    public static function orientationProvider(): array
    {
        $red = [255, 0, 0, 0];
        $green = [0, 255, 0, 0];
        $blue = [0, 0, 255, 0];
        $yellow = [255, 255, 0, 0];

        return [
            'horizontal flip' => [2, 20, 10, [$green, $red, $yellow, $blue]],
            'horizontal flip and counter-clockwise rotation' => [7, 10, 20, [$yellow, $green, $blue, $red]],
            'horizontal flip and clockwise rotation' => [5, 10, 20, [$red, $blue, $green, $yellow]],
            'invalid' => [9, 20, 10, [$red, $green, $blue, $yellow]],
            'normal' => [1, 20, 10, [$red, $green, $blue, $yellow]],
            'rotate 180' => [3, 20, 10, [$yellow, $blue, $green, $red]],
            'rotate clockwise' => [6, 10, 20, [$blue, $red, $yellow, $green]],
            'rotate counter-clockwise' => [8, 10, 20, [$green, $yellow, $red, $blue]],
            'vertical flip' => [4, 20, 10, [$blue, $yellow, $red, $green]],
        ];
    }

    /**
     * @param array{
     *     array{int, int, int, int},
     *     array{int, int, int, int},
     *     array{int, int, int, int},
     *     array{int, int, int, int}
     * } $expected
     */
    #[DataProvider('orientationProvider')]
    #[RequiresPhpExtension('exif')]
    public function testOrient(int $orientation, int $width, int $height, array $expected): void
    {
        $image1 = $this->createOrientedImage($orientation);
        $image2 = $image1->orient();

        $this->assertSame($image1, $image2);
        $this->assertSame($width, $image2->getWidth());
        $this->assertSame($height, $image2->getHeight());

        [$topLeft, $topRight, $bottomLeft, $bottomRight] = $expected;

        $this->assertEqualsWithDelta($topLeft, $this->getPixel($image2, 0, 0), 2);
        $this->assertEqualsWithDelta($topRight, $this->getPixel($image2, $width - 1, 0), 2);
        $this->assertEqualsWithDelta($bottomLeft, $this->getPixel($image2, 0, $height - 1), 2);
        $this->assertEqualsWithDelta($bottomRight, $this->getPixel($image2, $width - 1, $height - 1), 2);
    }

    protected function createOrientedImage(int $orientation): Image
    {
        $gdImage = $this->createImageData(20, 10) |> imagecreatefromstring(...);

        $this->assertInstanceOf(GdImage::class, $gdImage);

        ob_start();
        imagejpeg($gdImage, quality: 100);

        $data = ob_get_clean();

        $exif = "Exif\0\0II*\0\x08\0\0\0\x01\0\x12\x01\x03\0\x01\0\0\0".
            pack('V', $orientation).
            "\0\0\0\0";
        $exifLength = pack('n', strlen($exif) + 2);

        $data = substr($data, 0, 2).
            "\xff\xe1".
            $exifLength.
            $exif.
            substr($data, 2);

        file_put_contents('tmp/oriented.jpg', $data);

        return Image::createFromFile('tmp/oriented.jpg');
    }
}

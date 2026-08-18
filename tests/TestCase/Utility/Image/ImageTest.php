<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Core\Traits\StaticMacroTrait;
use Fyre\Utility\Image;
use GdImage;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function array_values;
use function class_uses;
use function imagecolorat;
use function imagecolorsforindex;
use function imagecreatefromstring;
use function imagecreatetruecolor;
use function imagefill;
use function imagefilledrectangle;
use function imagepng;
use function imagesavealpha;
use function mkdir;
use function ob_get_clean;
use function ob_start;
use function rmdir;
use function unlink;

#[RequiresPhpExtension('gd')]
final class ImageTest extends TestCase
{
    use BlurTestTrait;
    use BrightnessTestTrait;
    use ContainTestTrait;
    use ContrastTestTrait;
    use CoverTestTrait;
    use CreateFromFileTestTrait;
    use CreateFromStringTestTrait;
    use CropTestTrait;
    use DominantColorTestTrait;
    use FitTestTrait;
    use FlipHorizontalTestTrait;
    use FlipVerticalTestTrait;
    use GrayscaleTestTrait;
    use OrientTestTrait;
    use PixelateTestTrait;
    use ResizeTestTrait;
    use RotateTestTrait;
    use SaveTestTrait;
    use SharpenTestTrait;
    use ToBase64TestTrait;
    use ToBinaryTestTrait;
    use ToDataUriTestTrait;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Image::class)
        );
    }

    public function testMacro(): void
    {
        $this->assertEmpty(
            array_diff([MacroTrait::class, StaticMacroTrait::class], class_uses(Image::class))
        );
    }

    protected function createImage(int $width = 2, int $height = 2): Image
    {
        return $this->createImageData($width, $height) |> Image::createFromString(...);
    }

    protected function createImageData(int $width = 2, int $height = 2): string
    {
        if ($width < 1 || $height < 1) {
            throw new InvalidArgumentException('Image dimensions must be greater than 0.');
        }

        $image = imagecreatetruecolor($width, $height);

        imagesavealpha($image, true);

        $red = 0xFF0000;
        $green = 0x00FF00;
        $blue = 0x0000FF;
        $yellow = 0xFFFF00;

        $middleX = (int) ($width / 2);
        $middleY = (int) ($height / 2);
        $maxX = $width - 1;
        $maxY = $height - 1;

        imagefill($image, 0, 0, $red);
        imagefilledrectangle($image, $middleX, 0, $maxX, $middleY - 1, $green);
        imagefilledrectangle($image, 0, $middleY, $middleX - 1, $maxY, $blue);
        imagefilledrectangle($image, $middleX, $middleY, $maxX, $maxY, $yellow);

        ob_start();
        imagepng($image);
        $data = ob_get_clean();

        return $data;
    }

    /**
     * @return array{int, int, int, int}
     */
    protected function getPixel(Image $image, int $x, int $y): array
    {
        $gdImage = $image->toBinary() |> imagecreatefromstring(...);

        $this->assertInstanceOf(GdImage::class, $gdImage);

        $color = (int) imagecolorat($gdImage, $x, $y);

        return imagecolorsforindex($gdImage, $color) |> array_values(...);
    }

    #[Override]
    protected function setUp(): void
    {
        @mkdir('tmp');
    }

    #[Override]
    protected function tearDown(): void
    {
        @unlink('tmp/oriented.jpg');
        @unlink('tmp/test.png');
        @rmdir('tmp');
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

use const INF;

trait RotateTestTrait
{
    /**
     * @return array<string, array{float}>
     */
    public static function noOpRotationProvider(): array
    {
        return [
            'full rotation' => [360.0],
            'negative full rotation' => [-360.0],
            'zero' => [0.0],
        ];
    }

    public function testRotate(): void
    {
        $image1 = $this->createImage();
        $image2 = $image1->rotate(90);

        $this->assertSame($image1, $image2);
        $this->assertSame(2, $image2->getWidth());
        $this->assertSame(2, $image2->getHeight());
        $this->assertArraysAreIdentical([0, 0, 255, 0], $this->getPixel($image2, 0, 0));
        $this->assertArraysAreIdentical([255, 0, 0, 0], $this->getPixel($image2, 1, 0));
        $this->assertArraysAreIdentical([255, 255, 0, 0], $this->getPixel($image2, 0, 1));
        $this->assertArraysAreIdentical([0, 255, 0, 0], $this->getPixel($image2, 1, 1));
    }

    public function testRotateBackground(): void
    {
        $image1 = $this->createImage(20, 10);
        $image2 = $image1->rotate(45, '#123456');

        $this->assertSame($image1, $image2);
        $this->assertArraysAreIdentical([18, 52, 86, 0], $this->getPixel($image2, 0, 0));
    }

    public function testRotateInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Rotation must be a finite number.');

        $image = $this->createImage();
        $image->rotate(INF);
    }

    #[DataProvider('noOpRotationProvider')]
    public function testRotateNoOp(float $degrees): void
    {
        $image1 = $this->createImage();
        $data = $image1->toBinary();
        $image2 = $image1->rotate($degrees);

        $this->assertSame($image1, $image2);
        $this->assertSame($data, $image2->toBinary());
    }
}

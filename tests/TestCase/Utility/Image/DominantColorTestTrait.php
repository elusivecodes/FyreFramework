<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

trait DominantColorTestTrait
{
    public function testDominantColor(): void
    {
        $image = $this->createImage();

        $this->assertSame('#808040', $image->dominantColor());
        $this->assertSame(2, $image->getWidth());
        $this->assertSame(2, $image->getHeight());
    }
}

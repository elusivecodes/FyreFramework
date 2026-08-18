<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

use function base64_encode;

trait ToBase64TestTrait
{
    public function testToBase64(): void
    {
        $image = $this->createImage();

        $this->assertSame(base64_encode($image->toBinary()), $image->toBase64());
    }
}

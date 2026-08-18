<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Image;

trait ToDataUriTestTrait
{
    public function testToDataUri(): void
    {
        $image = $this->createImage();
        $data = $image->toDataUri();

        $this->assertStringStartsWith('data:image/png;base64,', $data);
    }
}

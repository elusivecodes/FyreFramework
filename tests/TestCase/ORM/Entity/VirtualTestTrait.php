<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Entity;

use Fyre\ORM\Entity;

trait VirtualTestTrait
{
    public function testSetVirtual(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity->setVirtual(['test']),
            $entity
        );

        $this->assertArraysAreIdentical(
            [
                'test',
            ],
            $entity->getVirtual()
        );
    }

    public function testSetVirtualMerge(): void
    {
        $entity = new Entity();

        $entity->setVirtual(['test1']);
        $entity->setVirtual(['test2'], true);

        $this->assertArraysAreIdentical(
            [
                'test1',
                'test2',
            ],
            $entity->getVirtual()
        );
    }

    public function testSetVirtualOverwrite(): void
    {
        $entity = new Entity();

        $entity->setVirtual(['test1']);
        $entity->setVirtual(['test2']);

        $this->assertArraysAreIdentical(
            [
                'test2',
            ],
            $entity->getVirtual()
        );
    }

    public function testSetVirtualVisible(): void
    {
        $entity = new Entity();

        $entity->setVirtual(['test']);

        $this->assertArraysAreIdentical(
            [
                'test',
            ],
            $entity->getVisible()
        );
    }
}

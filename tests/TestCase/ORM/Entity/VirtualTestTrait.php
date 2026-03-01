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

        $this->assertSame(
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

        $this->assertSame(
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

        $this->assertSame(
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

        $this->assertSame(
            [
                'test',
            ],
            $entity->getVisible()
        );
    }
}

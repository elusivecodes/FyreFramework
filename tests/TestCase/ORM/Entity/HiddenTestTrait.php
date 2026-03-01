<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Entity;

use Fyre\ORM\Entity;

trait HiddenTestTrait
{
    public function testSetHidden(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->setHidden(['test'])
        );

        $this->assertSame(
            [
                'test',
            ],
            $entity->getHidden()
        );
    }

    public function testSetHiddenMerge(): void
    {
        $entity = new Entity();

        $entity->setHidden(['test1']);
        $entity->setHidden(['test2'], true);

        $this->assertSame(
            [
                'test1',
                'test2',
            ],
            $entity->getHidden()
        );
    }

    public function testSetHiddenNotVisible(): void
    {
        $entity = new Entity();

        $entity->set('test', 1);
        $entity->setHidden(['test']);

        $this->assertSame(
            [],
            $entity->getVisible()
        );
    }

    public function testSetHiddenOverwrite(): void
    {
        $entity = new Entity();

        $entity->setHidden(['test1']);
        $entity->setHidden(['test2']);

        $this->assertSame(
            [
                'test2',
            ],
            $entity->getHidden()
        );
    }
}

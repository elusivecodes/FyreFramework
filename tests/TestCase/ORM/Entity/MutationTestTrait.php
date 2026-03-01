<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Entity;

use Tests\Mock\Entities\MockEntity;

trait MutationTestTrait
{
    public function testExtractMutation(): void
    {
        $entity = new MockEntity();

        $entity->set('decimal', 2.5);

        $this->assertSame(
            [
                'decimal' => '2.50',
            ],
            $entity->extract(['decimal'])
        );
    }

    public function testExtractOriginalFallbackNoMutation(): void
    {
        $entity = new MockEntity([
            'decimal' => 1,
        ]);

        $this->assertSame(
            [
                'decimal' => 1,
            ],
            $entity->extractOriginal(['decimal'])
        );
    }

    public function testExtractOriginalNoMutation(): void
    {
        $entity = new MockEntity([
            'decimal' => 2,
        ]);

        $entity->set('decimal', 2.5);

        $this->assertSame(
            [
                'decimal' => 2,
            ],
            $entity->extractOriginal(['decimal'])
        );
    }

    public function testFillMutation(): void
    {
        $entity = new MockEntity();

        $entity->fill([
            'integer' => 2.5,
        ]);

        $this->assertSame(
            2.0,
            $entity->get('integer')
        );
    }

    public function testFillNoMutation(): void
    {
        $entity = new MockEntity();

        $entity->fill([
            'integer' => 2.5,
        ], mutate: false);

        $this->assertSame(
            2.5,
            $entity->get('integer')
        );
    }

    public function testGetMutation(): void
    {
        $entity = new MockEntity();

        $entity->set('decimal', 2.5);

        $this->assertSame(
            '2.50',
            $entity->get('decimal')
        );
    }

    public function testGetOriginalFallbackNoMutation(): void
    {
        $entity = new MockEntity([
            'decimal' => 1,
        ]);

        $this->assertSame(
            1,
            $entity->getOriginal('decimal')
        );
    }

    public function testGetOriginalNoMutation(): void
    {
        $entity = new MockEntity([
            'decimal' => 2,
        ]);

        $entity->set('decimal', 2.5);

        $this->assertSame(
            2,
            $entity->getOriginal('decimal')
        );
    }

    public function testInitMutation(): void
    {
        $entity = new MockEntity([
            'integer' => 2.5,
        ]);

        $this->assertSame(
            2.0,
            $entity->get('integer')
        );
    }

    public function testMagicGetMutation(): void
    {
        $entity = new MockEntity();

        $entity->set('decimal', 2.5);

        $this->assertSame(
            '2.50',
            $entity->decimal
        );
    }

    public function testMagicSetMutation(): void
    {
        $entity = new MockEntity();

        $entity->integer = 2.5;

        $this->assertSame(
            2.0,
            $entity->get('integer')
        );
    }

    public function testSetMutation(): void
    {
        $entity = new MockEntity();

        $entity->set('integer', 2.5);

        $this->assertSame(
            2.0,
            $entity->get('integer')
        );
    }

    public function testSetNoMutation(): void
    {
        $entity = new MockEntity();

        $entity->set('integer', 2.5, mutate: false);

        $this->assertSame(
            2.5,
            $entity->get('integer')
        );
    }
}

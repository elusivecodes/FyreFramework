<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Entity;

use Fyre\ORM\Entity;
use Fyre\Utility\DateTime\DateTime;

use function json_encode;

use const JSON_PRETTY_PRINT;

trait MagicTestTrait
{
    public function testArrayAccessGet(): void
    {
        $entity = new Entity();

        $entity->set('test', 2);

        $this->assertSame(
            2,
            $entity['test']
        );
    }

    public function testArrayAccessIsset(): void
    {
        $entity = new Entity([
            'test' => 2,
        ]);

        $this->assertTrue(
            isset($entity['test'])
        );
    }

    public function testArrayAccessIssetEmpty(): void
    {
        $entity = new Entity([
            'test' => '',
        ]);

        $this->assertTrue(
            isset($entity['test'])
        );
    }

    public function testArrayAccessIssetInvalid(): void
    {
        $entity = new Entity();

        $this->assertFalse(
            isset($entity['invalid'])
        );
    }

    public function testArrayAccessSet(): void
    {
        $entity = new Entity();

        $entity['test'] = 2;

        $this->assertSame(
            2,
            $entity->get('test')
        );
    }

    public function testArrayAccessUnset(): void
    {
        $entity = new Entity([
            'test' => 2,
        ]);

        unset($entity['test']);

        $this->assertNull(
            $entity->get('test')
        );
    }

    public function testMagicGet(): void
    {
        $entity = new Entity();

        $entity->set('test', 2);

        $this->assertSame(
            2,
            $entity->test
        );
    }

    public function testMagicIsset(): void
    {
        $entity = new Entity([
            'test' => 2,
        ]);

        $this->assertTrue(
            isset($entity->test)
        );
    }

    public function testMagicIssetEmpty(): void
    {
        $entity = new Entity([
            'test' => '',
        ]);

        $this->assertTrue(
            isset($entity->test)
        );
    }

    public function testMagicIssetInvalid(): void
    {
        $entity = new Entity();

        $this->assertFalse(
            isset($entity->invalid)
        );
    }

    public function testMagicJson(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $this->assertSame(
            json_encode($entity->toArray(), JSON_PRETTY_PRINT),
            json_encode($entity, JSON_PRETTY_PRINT)
        );
    }

    public function testMagicJsonDateTime(): void
    {
        $entity = new Entity([
            'test' => new DateTime('2022-01-01'),
        ]);

        $this->assertSame(
            json_encode(['test' => '2022-01-01T00:00:00.000+00:00'], JSON_PRETTY_PRINT),
            json_encode($entity, JSON_PRETTY_PRINT)
        );
    }

    public function testMagicJsonDeep(): void
    {
        $child = new Entity([
            'test' => 1,
        ]);
        $parent = new Entity([
            'child' => $child,
        ]);

        $this->assertSame(
            json_encode($parent->toArray(), JSON_PRETTY_PRINT),
            json_encode($parent, JSON_PRETTY_PRINT)
        );
    }

    public function testMagicJsonNested(): void
    {
        $child = new Entity([
            'test' => 1,
        ]);
        $parent = new Entity([
            'children' => [$child],
        ]);

        $this->assertSame(
            json_encode($parent->toArray(), JSON_PRETTY_PRINT),
            json_encode($parent, JSON_PRETTY_PRINT)
        );
    }

    public function testMagicSet(): void
    {
        $entity = new Entity();

        $entity->test = 2;

        $this->assertSame(
            2,
            $entity->get('test')
        );
    }

    public function testMagicToString(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $this->assertSame(
            json_encode($entity->toArray(), JSON_PRETTY_PRINT),
            $entity->__toString()
        );
    }

    public function testMagicToStringDateTime(): void
    {
        $entity = new Entity([
            'test' => new DateTime('2022-01-01'),
        ]);

        $this->assertSame(
            json_encode(['test' => '2022-01-01T00:00:00.000+00:00'], JSON_PRETTY_PRINT),
            $entity->__toString()
        );
    }

    public function testMagicToStringDeep(): void
    {
        $child = new Entity([
            'test' => 1,
        ]);
        $parent = new Entity([
            'child' => $child,
        ]);

        $this->assertSame(
            json_encode($parent->toArray(), JSON_PRETTY_PRINT),
            $parent->__toString()
        );
    }

    public function testMagicToStringNested(): void
    {
        $child = new Entity([
            'test' => 1,
        ]);
        $parent = new Entity([
            'children' => [$child],
        ]);

        $this->assertSame(
            json_encode($parent->toArray(), JSON_PRETTY_PRINT),
            $parent->__toString()
        );
    }

    public function testMagicUnset(): void
    {
        $entity = new Entity([
            'test' => 2,
        ]);

        unset($entity->test);

        $this->assertNull(
            $entity->get('test')
        );
    }

    public function testToArray(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $this->assertSame(
            [
                'test' => 1,
            ],
            $entity->toArray()
        );
    }

    public function testToArrayDeep(): void
    {
        $child = new Entity([
            'test' => 1,
        ]);
        $parent = new Entity([
            'child' => $child,
        ]);

        $this->assertSame(
            [
                'child' => [
                    'test' => 1,
                ],
            ],
            $parent->toArray()
        );
    }

    public function testToArrayNested(): void
    {
        $child = new Entity([
            'test' => 1,
        ]);
        $parent = new Entity([
            'children' => [$child],
        ]);

        $this->assertSame(
            [
                'children' => [
                    [
                        'test' => 1,
                    ],
                ],
            ],
            $parent->toArray()
        );
    }

    public function testToJson(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $this->assertSame(
            json_encode($entity->toArray(), JSON_PRETTY_PRINT),
            $entity->toJson()
        );
    }

    public function testToJsonDateTime(): void
    {
        $entity = new Entity([
            'test' => new DateTime('2022-01-01'),
        ]);

        $this->assertSame(
            json_encode(['test' => '2022-01-01T00:00:00.000+00:00'], JSON_PRETTY_PRINT),
            $entity->toJson()
        );
    }

    public function testToJsonDeep(): void
    {
        $child = new Entity([
            'test' => 1,
        ]);
        $parent = new Entity([
            'child' => $child,
        ]);

        $this->assertSame(
            json_encode($parent->toArray(), JSON_PRETTY_PRINT),
            $parent->toJson()
        );
    }

    public function testToJsonNested(): void
    {
        $child = new Entity([
            'test' => 1,
        ]);
        $parent = new Entity([
            'children' => [$child],
        ]);

        $this->assertSame(
            json_encode($parent->toArray(), JSON_PRETTY_PRINT),
            $parent->toJson()
        );
    }
}

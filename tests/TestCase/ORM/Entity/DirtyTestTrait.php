<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Entity;

use Fyre\ORM\Entity;
use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Time;
use stdClass;

trait DirtyTestTrait
{
    public function testCleanDirty(): void
    {
        $entity = new Entity();

        $entity->setDirty('test');
        $entity->clean();

        $this->assertFalse(
            $entity->isDirty('test')
        );
    }

    public function testCleanFields(): void
    {
        $entity = new Entity([
            'name' => 'Original',
        ]);

        $entity->set('name', 'Updated');
        $entity->set('tags', ['test']);
        $entity->setError('name', 'error');
        $entity->setInvalid('name', 'invalid');

        $this->assertSame(
            $entity,
            $entity->cleanFields(['name' => 'Updated', 'tags' => ['test']])
        );
        $this->assertFalse(
            $entity->isDirty()
        );
        $this->assertSame(
            'Updated',
            $entity->getOriginal('name')
        );
        $this->assertSame(
            [],
            $entity->getErrors()
        );
        $this->assertSame(
            [],
            $entity->getInvalid()
        );
    }

    public function testCleanFieldsPreservesLaterChanges(): void
    {
        $entity = new Entity([
            'name' => 'Original',
        ]);

        $entity->set('name', 'Later');
        $entity->set('other', null);
        $entity->setError('name', 'error');
        $entity->setInvalid('name', 'invalid');
        $entity->cleanFields(['name' => 'Saved']);

        $this->assertSame(
            'Later',
            $entity->get('name')
        );
        $this->assertSame(
            'Saved',
            $entity->getOriginal('name')
        );
        $this->assertSame(
            ['name', 'other'],
            $entity->getDirty()
        );
        $this->assertSame(
            ['error'],
            $entity->getError('name')
        );
        $this->assertSame(
            'invalid',
            $entity->getInvalid('name')
        );
    }

    public function testCleanFieldsSkipsUnsetFields(): void
    {
        $entity = new Entity([
            'name' => 'Original',
        ]);

        $entity->unset('name');
        $entity->cleanFields(['name' => 'Saved']);

        $this->assertFalse(
            $entity->has('name')
        );
        $this->assertFalse(
            $entity->isDirty()
        );
    }

    public function testClearDirty(): void
    {
        $entity = new Entity();

        $entity->setDirty('test');
        $entity->clear(['test']);

        $this->assertFalse(
            $entity->isDirty('test')
        );
    }

    public function testExtractDirty(): void
    {
        $entity = new Entity([
            'test1' => 1,
            'test2' => 2,
            'test3' => 3,
        ]);

        $entity->setDirty('test2');

        $this->assertArraysAreIdentical(
            [
                'test2' => 2,
            ],
            $entity->extractDirty()
        );
    }

    public function testExtractDirtyFields(): void
    {
        $entity = new Entity([
            'test1' => 1,
            'test2' => 2,
            'test3' => 3,
        ]);

        $entity->setDirty('test2');

        $this->assertArraysAreIdentical(
            [
                'test2' => 2,
            ],
            $entity->extractDirty(['test2', 'test3'])
        );
    }

    public function testIsDirtyFalseSetEqualDate(): void
    {
        $entity = new Entity([
            'test' => Date::createFromArray([2022, 1, 1]),
        ]);

        $entity->set('test', Date::createFromArray([2022, 1, 1]));

        $this->assertFalse(
            $entity->isDirty('test')
        );
    }

    public function testIsDirtyFalseSetEqualDateTime(): void
    {
        $entity = new Entity([
            'test' => DateTime::createFromArray([2022, 1, 1, 12, 30, 15]),
        ]);

        $entity->set('test', DateTime::createFromArray([2022, 1, 1, 12, 30, 15]));

        $this->assertFalse(
            $entity->isDirty('test')
        );
    }

    public function testIsDirtyFalseSetEqualObject(): void
    {
        $value = new stdClass();
        $value->test = 1;

        $entity = new Entity([
            'test' => $value,
        ]);

        $newValue = new stdClass();
        $newValue->test = 1;
        $entity->set('test', $newValue);

        $this->assertFalse(
            $entity->isDirty('test')
        );
    }

    public function testIsDirtyFalseSetEqualTime(): void
    {
        $entity = new Entity([
            'test' => Time::createFromArray([12, 30, 15]),
        ]);

        $entity->set('test', Time::createFromArray([12, 30, 15]));

        $this->assertFalse(
            $entity->isDirty('test')
        );
    }

    public function testIsDirtyFalseSetSameValue(): void
    {
        $entity = new Entity([
            'test' => 2,
        ]);

        $entity->set('test', 2);

        $this->assertFalse(
            $entity->isDirty('test')
        );
    }

    public function testIsDirtyFromSet(): void
    {
        $entity = new Entity();

        $entity->set('test', 2);

        $this->assertTrue(
            $entity->isDirty('test')
        );
    }

    public function testIsDirtyInvalid(): void
    {
        $entity = new Entity();

        $this->assertFalse(
            $entity->isDirty('invalid')
        );
    }

    public function testIsDirtySetDifferentClass(): void
    {
        $date = Date::createFromArray([1970, 1, 1]);
        $time = Time::createFromArray([0]);

        $entity = new Entity([
            'test' => $date,
        ]);

        $entity->set('test', $time);

        $this->assertTrue(
            $entity->isDirty('test')
        );
    }

    public function testIsDirtySetDifferentDate(): void
    {
        $entity = new Entity([
            'test' => Date::createFromArray([2022, 1, 1]),
        ]);

        $entity->set('test', Date::createFromArray([2022, 1, 2]));

        $this->assertTrue(
            $entity->isDirty('test')
        );
    }

    public function testIsDirtySetDifferentDateTime(): void
    {
        $entity = new Entity([
            'test' => DateTime::createFromArray([2022, 1, 1, 12, 30, 15]),
        ]);

        $entity->set('test', DateTime::createFromArray([2022, 1, 1, 12, 30, 16]));

        $this->assertTrue(
            $entity->isDirty('test')
        );
    }

    public function testIsDirtySetDifferentTime(): void
    {
        $entity = new Entity([
            'test' => Time::createFromArray([12, 30, 15]),
        ]);

        $entity->set('test', Time::createFromArray([12, 30, 16]));

        $this->assertTrue(
            $entity->isDirty('test')
        );
    }

    public function testSetDirty(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->setDirty('test')
        );

        $this->assertTrue(
            $entity->isDirty('test')
        );
    }

    public function testSetDirtyFalse(): void
    {
        $entity = new Entity();

        $entity->set('test', 2);
        $entity->setDirty('test', false);

        $this->assertFalse(
            $entity->isDirty('test')
        );
    }

    public function testUnsetDirty(): void
    {
        $entity = new Entity();

        $entity->setDirty('test');
        $entity->unset('test');

        $this->assertFalse(
            $entity->isDirty('test')
        );
    }
}

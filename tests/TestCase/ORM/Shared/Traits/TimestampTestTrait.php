<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Traits;

use Fyre\Utility\DateTime\DateTime;
use Tests\Mock\Entities\Timestamp;

trait TimestampTestTrait
{
    public function testTimestampsCreate(): void
    {
        $Timestamps = $this->modelRegistry->use('Timestamps');

        $timestamp = $Timestamps->newEmptyEntity();

        $this->assertTrue(
            $Timestamps->save($timestamp)
        );

        $timestamp = $Timestamps->find()->first();

        $this->assertInstanceOf(
            Timestamp::class,
            $timestamp
        );

        $this->assertInstanceOf(
            DateTime::class,
            $timestamp->created
        );

        $this->assertInstanceOf(
            DateTime::class,
            $timestamp->modified
        );
    }

    public function testTimestampsOuterRollback(): void
    {
        $Timestamps = $this->modelRegistry->use('Timestamps');
        $timestamp = $Timestamps->newEntity([
            'created' => DateTime::now()->subHours(2),
            'modified' => DateTime::now()->subHours(1),
        ]);

        $this->assertTrue(
            $Timestamps->save($timestamp, events: false)
        );

        $created = $timestamp->created;
        $modified = $timestamp->modified;
        $timestamp->setDirty('modified');
        $this->db->begin();

        $this->assertTrue(
            $Timestamps->save($timestamp)
        );
        $this->assertNotSame(
            $modified,
            $timestamp->modified
        );

        $this->db->rollback();

        $this->assertSame(
            $created,
            $timestamp->created
        );
        $this->assertSame(
            $modified,
            $timestamp->modified
        );
        $this->assertFalse(
            $timestamp->isNew()
        );
    }

    public function testTimestampsUpdate(): void
    {
        $Timestamps = $this->modelRegistry->use('Timestamps');

        $timestamp = $Timestamps->newEmptyEntity();

        $this->assertTrue(
            $Timestamps->save($timestamp)
        );

        $timestamp = $Timestamps->find()->first();

        $this->assertInstanceOf(
            Timestamp::class,
            $timestamp
        );

        $this->assertInstanceOf(
            DateTime::class,
            $timestamp->modified
        );

        $originalModified = DateTime::now()->subSeconds(1);

        $timestamp->set('modified', $originalModified);
        $timestamp->setDirty('created', true);

        $this->assertTrue(
            $Timestamps->save($timestamp)
        );

        $timestamp = $Timestamps->find()->first();

        $this->assertInstanceOf(
            Timestamp::class,
            $timestamp
        );

        $this->assertInstanceOf(
            DateTime::class,
            $timestamp->modified
        );

        $this->assertNotSame(
            $originalModified->toIsoString(),
            $timestamp->modified->toIsoString()
        );
    }
}

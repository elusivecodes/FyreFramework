<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Traits;

use Fyre\Utility\DateTime\DateTime;
use Tests\Mock\Entities\Timestamp;

use function sleep;

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

        $originalModified = $timestamp->modified->toIsoString();

        $timestamp->setDirty('created', true);

        sleep(1);

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
            $originalModified,
            $timestamp->modified->toIsoString()
        );
    }
}

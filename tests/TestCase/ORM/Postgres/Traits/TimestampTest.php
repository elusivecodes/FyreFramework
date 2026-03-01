<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Postgres\Traits;

use Fyre\Utility\DateTime\DateTime;
use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\Postgres\PostgresConnectionTrait;

use function sleep;

final class TimestampTest extends TestCase
{
    use PostgresConnectionTrait;

    public function testTimestampsCreate(): void
    {
        $Timestamps = $this->modelRegistry->use('Timestamps');

        $timestamp = $Timestamps->newEmptyEntity();

        $this->assertTrue(
            $Timestamps->save($timestamp)
        );

        $timestamp = $Timestamps->find()->first();

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

        $originalModified = $timestamp->modified->toIsoString();

        $timestamp->setDirty('created', true);

        sleep(1);

        $this->assertTrue(
            $Timestamps->save($timestamp)
        );

        $timestamp = $Timestamps->find()->first();

        $this->assertNotSame(
            $originalModified,
            $timestamp->modified->toIsoString()
        );
    }
}

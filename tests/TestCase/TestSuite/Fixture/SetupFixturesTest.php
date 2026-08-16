<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Fixture;

use Fyre\DB\Exceptions\DbException;
use Fyre\ORM\Entity;
use Fyre\TestSuite\TestCase;
use InvalidArgumentException;

final class SetupFixturesTest extends TestCase
{
    use MysqlConnectionTrait;

    protected array $fixtures = [
        'Items',
    ];

    public function testRun(): void
    {
        $this->assertArraysAreIdentical(
            [
                [
                    'id' => 1,
                    'name' => 'Test 1',
                ],
                [
                    'id' => 2,
                    'name' => 'Test 2',
                ],
            ],
            $this->fixture->getModel()
                ->find()
                ->all()
                ->map(static fn(Entity $item): array => $item->toArray())
                ->toArray()
        );
    }

    public function testSetupFixturesEnablesForeignKeysAfterFailure(): void
    {
        $this->fixtures = ['Invalid'];

        try {
            $this->setupFixtures();
            $this->fail('Expected fixture setup to fail.');
        } catch (InvalidArgumentException $e) {
            $this->assertSame(
                'Fixture `Invalid` does not exist.',
                $e->getMessage()
            );
        } finally {
            $this->fixtures = ['Items'];
        }

        $row = $this->db->select('@@foreign_key_checks')
            ->execute()
            ->first();

        $this->assertIsArray($row);

        $this->assertSame(
            1,
            $row['@@foreign_key_checks']
        );
    }

    public function testTeardownFixturesEnablesForeignKeysAfterFailure(): void
    {
        $model = $this->fixture->getModel();
        $model->setTable('invalid');

        try {
            $this->teardownFixtures();
            $this->fail('Expected fixture teardown to fail.');
        } catch (DbException $e) {
            $this->assertStringStartsWith(
                'Database error: SQLSTATE[42S02]',
                $e->getMessage()
            );
        } finally {
            $model->setTable('items');
        }

        $row = $this->db->select('@@foreign_key_checks')
            ->execute()
            ->first();

        $this->assertIsArray($row);

        $this->assertSame(
            1,
            $row['@@foreign_key_checks']
        );
    }

    public function testTeardownFixturesTruncatesAssociatedTables(): void
    {
        $this->teardownFixtures();
        $this->fixtures = ['ItemsAssociated'];
        $this->setupFixtures();

        $this->assertSame(
            1,
            $this->modelRegistry->use('Items')
                ->find()
                ->count()
        );

        $this->assertSame(
            1,
            $this->modelRegistry->use('Children')
                ->find()
                ->count()
        );

        $this->teardownFixtures();

        $this->assertSame(
            0,
            $this->modelRegistry->use('Items')
                ->find()
                ->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Children')
                ->find()
                ->count()
        );
    }
}

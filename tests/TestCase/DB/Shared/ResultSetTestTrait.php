<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Shared;

use Fyre\DB\Types\StringType;
use LogicException;

trait ResultSetTestTrait
{
    public function testAll(): void
    {
        $this->insert();

        $this->assertSame(
            [
                [
                    'id' => 1,
                    'name' => 'Test 1',
                ],
                [
                    'id' => 2,
                    'name' => 'Test 2',
                ],
                [
                    'id' => 3,
                    'name' => 'Test 3',
                ],
            ],
            $this->db->select()
                ->from('test')
                ->execute()
                ->all()
        );
    }

    public function testClearBuffer(): void
    {
        $this->insert();

        $result = $this->db->select()
            ->from('test')
            ->execute();

        $result->all();
        $result->clearBuffer(1);

        $this->assertSame(
            [
                [
                    'id' => 1,
                    'name' => 'Test 1',
                ],
                [],
                [
                    'id' => 3,
                    'name' => 'Test 3',
                ],
            ],
            $result->all()
        );
    }

    public function testClearBufferAll(): void
    {
        $this->insert();

        $result = $this->db->select()
            ->from('test')
            ->execute();

        $result->first();
        $result->clearBuffer();

        $this->assertSame(
            [
                [],
                [
                    'id' => 2,
                    'name' => 'Test 2',
                ],
                [
                    'id' => 3,
                    'name' => 'Test 3',
                ],
            ],
            $result->all()
        );
    }

    public function testColumnCount(): void
    {
        $this->insert();

        $this->assertSame(
            2,
            $this->db->select()
                ->from('test')
                ->execute()
                ->columnCount()
        );
    }

    public function testColumns(): void
    {
        $this->insert();

        $this->assertSame(
            [
                'id',
                'name',
            ],
            $this->db->select()
                ->from('test')
                ->execute()
                ->columns()
        );
    }

    public function testCount(): void
    {
        $this->insert();

        $this->assertSame(
            3,
            $this->db->select()
                ->from('test')
                ->execute()
                ->count()
        );
    }

    public function testDecorateAfterBuffering(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Result decorators cannot be added after buffering has started.');

        $this->insert();

        $result = $this->db->select()
            ->from('test')
            ->execute();

        $result->all();
        $result->decorate(static fn(array $row): array => $row);
    }

    public function testDecorateMultiple(): void
    {
        $this->insert();

        $result = $this->db->select()
            ->from('test')
            ->execute()
            ->decorate(static function(array $row): array {
                $row['value'] = $row['id'];

                return $row;
            })
            ->decorate(static function(array $row): array {
                $row['value'] *= 2;

                return $row;
            });

        $this->assertSame(
            [
                [
                    'id' => 1,
                    'name' => 'Test 1',
                    'value' => 2,
                ],
                [
                    'id' => 2,
                    'name' => 'Test 2',
                    'value' => 4,
                ],
                [
                    'id' => 3,
                    'name' => 'Test 3',
                    'value' => 6,
                ],
            ],
            $result->all()
        );
    }

    public function testFetch(): void
    {
        $this->insert();

        $this->assertSame(
            [
                'id' => 2,
                'name' => 'Test 2',
            ],
            $this->db->select()
                ->from('test')
                ->execute()
                ->fetch(1)
        );
    }

    public function testFirst(): void
    {
        $this->insert();

        $this->assertSame(
            [
                'id' => 1,
                'name' => 'Test 1',
            ],
            $this->db->select()
                ->from('test')
                ->execute()
                ->first()
        );
    }

    public function testIteration(): void
    {
        $this->insert();

        $query = $this->db->select()
            ->from('test')
            ->execute();

        $results = [];

        foreach ($query as $row) {
            $results[] = $row;
        }

        $this->assertSame(
            [
                [
                    'id' => 1,
                    'name' => 'Test 1',
                ],
                [
                    'id' => 2,
                    'name' => 'Test 2',
                ],
                [
                    'id' => 3,
                    'name' => 'Test 3',
                ],
            ],
            $results
        );
    }

    public function testLast(): void
    {
        $this->insert();

        $this->assertSame(
            [
                'id' => 3,
                'name' => 'Test 3',
            ],
            $this->db->select()
                ->from('test')
                ->execute()
                ->last()
        );
    }

    public function testType(): void
    {
        $this->insert();

        $this->assertInstanceOf(
            StringType::class,
            $this->db->select()
                ->from('test')
                ->execute()
                ->getType('name')
        );
    }
}

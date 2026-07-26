<?php
declare(strict_types=1);

namespace Tests\TestCase\Core\Make;

use Fyre\Core\Container;
use Fyre\Core\Make\ModelSourceBuilder;
use Fyre\DB\Schema\Column;
use PHPUnit\Framework\TestCase;

final class ModelSourceBuilderEnumTest extends TestCase
{
    public function testInfer(): void
    {
        $builder = new Container()->use(ModelSourceBuilder::class);
        $column = $this->createStub(Column::class);
        $column->method('getName')->willReturn('status');
        $column->method('getComment')->willReturn(
            ' [enum] Draft, Published:published, Archived: '
        );

        $this->assertSame(
            [[
                'field' => 'status',
                'className' => 'PostStatus',
                'cases' => 'Draft, Published:published, Archived:',
                'values' => ['draft', 'published', 'archived'],
            ]],
            $builder->inferEnums([$column], 'Post')
        );
    }

    public function testInferClassName(): void
    {
        $builder = new Container()->use(ModelSourceBuilder::class);
        $column = $this->createStub(Column::class);
        $column->method('getName')->willReturn('publication_state');
        $column->method('getComment')->willReturn('[enum] Draft, Published');

        $this->assertSame(
            [[
                'field' => 'publication_state',
                'className' => 'PostPublicationState',
                'cases' => 'Draft, Published',
                'values' => [],
            ]],
            $builder->inferEnums([$column], 'Post')
        );
    }

    public function testInferClassNameInComment(): void
    {
        $builder = new Container()->use(ModelSourceBuilder::class);
        $column = $this->createStub(Column::class);
        $column->method('getComment')->willReturn('[enum PublicationStatus] Draft');

        $this->assertSame(
            [],
            $builder->inferEnums([$column], 'Post')
        );
    }

    public function testInferInvalidComment(): void
    {
        $builder = new Container()->use(ModelSourceBuilder::class);
        $column = $this->createStub(Column::class);
        $column->method('getComment')->willReturn('Not an enum');

        $this->assertSame(
            [],
            $builder->inferEnums([$column], 'Post')
        );
    }
}

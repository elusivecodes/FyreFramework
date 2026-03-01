<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Core\Traits\StaticMacroTrait;
use Fyre\Utility\Arr;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class ArrTest extends TestCase
{
    use ChunkTestTrait;
    use CollapseTestTrait;
    use ColumnTestTrait;
    use CombineTestTrait;
    use CountTestTrait;
    use DiffTestTrait;
    use DivideTestTrait;
    use DotTestTrait;
    use EveryTestTrait;
    use ExceptTestTrait;
    use FillTestTrait;
    use FilterTestTrait;
    use FindKeyTestTrait;
    use FindLastKeyTestTrait;
    use FindLastTestTrait;
    use FindTestTrait;
    use FirstTestTrait;
    use FlattenTestTrait;
    use ForgetDotTestTrait;
    use GetDotTestTrait;
    use HasDotTestTrait;
    use HasKeyTestTrait;
    use IncludesTestTrait;
    use IndexOfTestTrait;
    use IndexTestTrait;
    use IntersectTestTrait;
    use IsArrayTestTrait;
    use IsListTestTrait;
    use JoinTestTrait;
    use KeysTestTrait;
    use LastIndexOfTestTrait;
    use LastTestTrait;
    use MapTestTrait;
    use MergeTestTrait;
    use NoneTestTrait;
    use OnlyTestTrait;
    use PadTestTrait;
    use PluckDotTestTrait;
    use PopTestTrait;
    use PushTestTrait;
    use RandomValueTestTrait;
    use RangeTestTrait;
    use ReduceTestTrait;
    use ReverseTestTrait;
    use SetDotTestTrait;
    use ShiftTestTrait;
    use ShuffleTestTrait;
    use SliceTestTrait;
    use SomeTestTrait;
    use SortTestTrait;
    use SpliceTestTrait;
    use UniqueTestTrait;
    use UnshiftTestTrait;
    use ValuesTestTrait;
    use WrapTestTrait;

    public function testMacro(): void
    {
        $this->assertContains(
            StaticMacroTrait::class,
            class_uses(Arr::class)
        );
    }
}

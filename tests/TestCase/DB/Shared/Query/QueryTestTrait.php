<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Shared\Query;

trait QueryTestTrait
{
    use DeleteTestTrait;
    use ExecuteTestTrait;
    use GetTestTrait;
    use InsertTestTrait;
    use PaginationTestTrait;
    use SelectTestTrait;
    use TransactionTestTrait;
    use UpdateTestTrait;
    use UpsertTestTrait;
}

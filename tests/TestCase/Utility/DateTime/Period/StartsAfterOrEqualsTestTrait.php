<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;

trait StartsAfterOrEqualsTestTrait
{
    public function testStartsAfterOrEquals(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15')
                ->startsAfterOrEquals(new DateTime('2022-01-01'))
        );
    }

    public function testStartsAfterOrEqualsAfter(): void
    {
        $this->assertFalse(
            new Period('2022-01-01', '2022-01-15')
                ->startsAfterOrEquals(new DateTime('2022-01-02'))
        );
    }

    public function testStartsAfterOrEqualsAfterExcludeStart(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'start')
                ->startsAfterOrEquals(new DateTime('2022-01-01'))
        );
    }

    public function testStartsAfterOrEqualsBefore(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15')
                ->startsAfterOrEquals(new DateTime('2021-12-31'))
        );
    }

    public function testStartsAfterOrEqualsBeforeExcludeStart(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'start')
                ->startsAfterOrEquals(new DateTime('2021-12-31'))
        );
    }
}

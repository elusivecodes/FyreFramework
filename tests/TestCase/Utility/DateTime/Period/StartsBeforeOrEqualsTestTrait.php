<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;

trait StartsBeforeOrEqualsTestTrait
{
    public function testStartsBeforeOrEquals(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15')
                ->startsBeforeOrEquals(new DateTime('2022-01-01'))
        );
    }

    public function testStartsBeforeOrEqualsAfter(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15')
                ->startsBeforeOrEquals(new DateTime('2022-01-02'))
        );
    }

    public function testStartsBeforeOrEqualsAfterExcludeStart(): void
    {
        $this->assertFalse(
            new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'start')
                ->startsBeforeOrEquals(new DateTime('2022-01-01'))
        );
    }

    public function testStartsBeforeOrEqualsBefore(): void
    {
        $this->assertFalse(
            new Period('2022-01-01', '2022-01-15')
                ->startsBeforeOrEquals(new DateTime('2021-12-31'))
        );
    }

    public function testStartsBeforeOrEqualsBeforeExcludeStart(): void
    {
        $this->assertFalse(
            new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'start')
                ->startsBeforeOrEquals(new DateTime('2021-12-31'))
        );
    }
}

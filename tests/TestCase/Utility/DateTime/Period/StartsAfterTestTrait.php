<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;

trait StartsAfterTestTrait
{
    public function testStartsAfter(): void
    {
        $this->assertFalse(
            new Period('2022-01-01', '2022-01-15')
                ->startsAfter(new DateTime('2022-01-01'))
        );
    }

    public function testStartsAfterAfter(): void
    {
        $this->assertFalse(
            new Period('2022-01-01', '2022-01-15')
                ->startsAfter(new DateTime('2022-01-02'))
        );
    }

    public function testStartsAfterAfterExcludeStart(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'start')
                ->startsAfter(new DateTime('2022-01-01'))
        );
    }

    public function testStartsAfterBefore(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15')
                ->startsAfter(new DateTime('2021-12-31'))
        );
    }

    public function testStartsAfterBeforeExcludeStart(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'start')
                ->startsAfter(new DateTime('2021-12-31'))
        );
    }
}

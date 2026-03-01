<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;

trait StartsBeforeTestTrait
{
    public function testStartsBefore(): void
    {
        $this->assertFalse(
            new Period('2022-01-01', '2022-01-15')
                ->startsBefore(new DateTime('2022-01-01'))
        );
    }

    public function testStartsBeforeAfter(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15')
                ->startsBefore(new DateTime('2022-01-02'))
        );
    }

    public function testStartsBeforeAfterExcludeStart(): void
    {
        $this->assertFalse(
            new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'start')
                ->startsBefore(new DateTime('2022-01-01'))
        );
    }

    public function testStartsBeforeBefore(): void
    {
        $this->assertFalse(
            new Period('2022-01-01', '2022-01-15')
                ->startsBefore(new DateTime('2021-12-31'))
        );
    }

    public function testStartsBeforeBeforeExcludeStart(): void
    {
        $this->assertFalse(
            new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'start')
                ->startsBefore(new DateTime('2021-12-31'))
        );
    }
}

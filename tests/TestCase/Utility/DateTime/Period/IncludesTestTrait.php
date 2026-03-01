<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;

trait IncludesTestTrait
{
    public function testIncludesEnd(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15')
                ->includes(new DateTime('2022-01-15'))
        );
    }

    public function testIncludesEndAfter(): void
    {
        $this->assertFalse(
            new Period('2022-01-01', '2022-01-15')
                ->includes(new DateTime('2022-01-16'))
        );
    }

    public function testIncludesEndAfterExcludeEnd(): void
    {
        $this->assertFalse(
            new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'end')
                ->includes(new DateTime('2022-01-15'))
        );
    }

    public function testIncludesEndBefore(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15')
                ->includes(new DateTime('2022-01-14'))
        );
    }

    public function testIncludesEndBeforeExcludeEnd(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'end')
                ->includes(new DateTime('2022-01-14'))
        );
    }

    public function testIncludesStart(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15')
                ->includes(new DateTime('2022-01-01'))
        );
    }

    public function testIncludesStartAfter(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15')
                ->includes(new DateTime('2022-01-02'))
        );
    }

    public function testIncludesStartAfterExcludeStart(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'start')
                ->includes(new DateTime('2022-01-02'))
        );
    }

    public function testIncludesStartBefore(): void
    {
        $this->assertFalse(
            new Period('2022-01-01', '2022-01-15')
                ->includes(new DateTime('2021-12-31'))
        );
    }

    public function testIncludesStartBeforeExcludeStart(): void
    {
        $this->assertFalse(
            new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'start')
                ->includes(new DateTime('2022-01-01'))
        );
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;

trait EndsAfterOrEqualsTestTrait
{
    public function testEndsAfterOrEquals(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15')
                ->endsAfterOrEquals(new DateTime('2022-01-15'))
        );
    }

    public function testEndsAfterOrEqualsAfter(): void
    {
        $this->assertFalse(
            new Period('2022-01-01', '2022-01-15')
                ->endsAfterOrEquals(new DateTime('2022-01-16'))
        );
    }

    public function testEndsAfterOrEqualsAfterExcludeEnd(): void
    {
        $this->assertFalse(
            new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'end')
                ->endsAfterOrEquals(new DateTime('2022-01-15'))
        );
    }

    public function testEndsAfterOrEqualsBefore(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15')
                ->endsAfterOrEquals(new DateTime('2022-01-14'))
        );
    }

    public function testEndsAfterOrEqualsBeforeExcludeEnd(): void
    {
        $this->assertTrue(
            new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'end')
                ->endsAfterOrEquals(new DateTime('2022-01-14'))
        );
    }
}

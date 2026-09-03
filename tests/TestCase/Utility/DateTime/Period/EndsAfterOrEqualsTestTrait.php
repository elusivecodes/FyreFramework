<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use PHPUnit\Framework\Attributes\DataProvider;

trait EndsAfterOrEqualsTestTrait
{
    /**
     * @return array<string, array{int[], 'end'|'none', bool}>
     */
    public static function endsAfterOrEqualsProvider(): array
    {
        return [
            'equal' => [[2022, 1, 15], 'none', true],
            'after' => [[2022, 1, 16], 'none', false],
            'before' => [[2022, 1, 14], 'none', true],
            'excluded end' => [[2022, 1, 15], 'end', false],
            'included end' => [[2022, 1, 14], 'end', true],
        ];
    }

    /**
     * @param int[] $date
     * @param 'end'|'none' $excludeBoundaries
     */
    #[DataProvider('endsAfterOrEqualsProvider')]
    public function testEndsAfterOrEquals(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->endsAfterOrEquals(DateTime::createFromArray($date)));
    }

    /**
     * @param int[] $date
     * @param 'end'|'none' $excludeBoundaries
     */
    #[DataProvider('endsAfterOrEqualsProvider')]
    public function testEndsAfterOrEqualsDate(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            Date::createFromArray([2022, 1, 1]),
            Date::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->endsAfterOrEquals(Date::createFromArray($date)));
    }
}

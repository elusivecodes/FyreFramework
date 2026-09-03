<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use PHPUnit\Framework\Attributes\DataProvider;

trait EndsAfterTestTrait
{
    /**
     * @return array<string, array{int[], 'end'|'none', bool}>
     */
    public static function endsAfterProvider(): array
    {
        return [
            'equal' => [[2022, 1, 15], 'none', false],
            'after' => [[2022, 1, 16], 'none', false],
            'before' => [[2022, 1, 14], 'none', true],
            'excluded end' => [[2022, 1, 15], 'end', false],
            'included end' => [[2022, 1, 14], 'end', false],
        ];
    }

    /**
     * @param int[] $date
     * @param 'end'|'none' $excludeBoundaries
     */
    #[DataProvider('endsAfterProvider')]
    public function testEndsAfter(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->endsAfter(DateTime::createFromArray($date)));
    }

    /**
     * @param int[] $date
     * @param 'end'|'none' $excludeBoundaries
     */
    #[DataProvider('endsAfterProvider')]
    public function testEndsAfterDate(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            Date::createFromArray([2022, 1, 1]),
            Date::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->endsAfter(Date::createFromArray($date)));
    }
}

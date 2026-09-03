<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use PHPUnit\Framework\Attributes\DataProvider;

trait EndsBeforeTestTrait
{
    /**
     * @return array<string, array{int[], 'end'|'none', bool}>
     */
    public static function endsBeforeProvider(): array
    {
        return [
            'equal' => [[2022, 1, 15], 'none', false],
            'after' => [[2022, 1, 16], 'none', true],
            'before' => [[2022, 1, 14], 'none', false],
            'excluded end' => [[2022, 1, 15], 'end', true],
            'included end' => [[2022, 1, 14], 'end', false],
        ];
    }

    /**
     * @param int[] $date
     * @param 'end'|'none' $excludeBoundaries
     */
    #[DataProvider('endsBeforeProvider')]
    public function testEndsBefore(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->endsBefore(DateTime::createFromArray($date)));
    }

    /**
     * @param int[] $date
     * @param 'end'|'none' $excludeBoundaries
     */
    #[DataProvider('endsBeforeProvider')]
    public function testEndsBeforeDate(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            Date::createFromArray([2022, 1, 1]),
            Date::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->endsBefore(Date::createFromArray($date)));
    }
}

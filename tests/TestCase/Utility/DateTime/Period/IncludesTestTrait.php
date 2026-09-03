<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use PHPUnit\Framework\Attributes\DataProvider;

trait IncludesTestTrait
{
    /**
     * @return array<string, array{int[], 'end'|'none'|'start', bool}>
     */
    public static function includesProvider(): array
    {
        return [
            'start' => [[2022, 1, 1], 'none', true],
            'end' => [[2022, 1, 15], 'none', true],
            'before' => [[2021, 12, 31], 'none', false],
            'after' => [[2022, 1, 16], 'none', false],
            'excluded start' => [[2022, 1, 1], 'start', false],
            'after excluded start' => [[2022, 1, 2], 'start', true],
            'excluded end' => [[2022, 1, 15], 'end', false],
            'before excluded end' => [[2022, 1, 14], 'end', true],
        ];
    }

    /**
     * @param int[] $date
     * @param 'end'|'none'|'start' $excludeBoundaries
     */
    #[DataProvider('includesProvider')]
    public function testIncludes(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->includes(DateTime::createFromArray($date)));
    }

    /**
     * @param int[] $date
     * @param 'end'|'none'|'start' $excludeBoundaries
     */
    #[DataProvider('includesProvider')]
    public function testIncludesDate(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            Date::createFromArray([2022, 1, 1]),
            Date::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->includes(Date::createFromArray($date)));
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use PHPUnit\Framework\Attributes\DataProvider;

trait EndEqualsTestTrait
{
    /**
     * @return array<string, array{int[], 'end'|'none', bool}>
     */
    public static function endEqualsProvider(): array
    {
        return [
            'equal' => [[2022, 1, 15], 'none', true],
            'before' => [[2022, 1, 14], 'none', false],
            'excluded end' => [[2022, 1, 15], 'end', false],
            'included end' => [[2022, 1, 14], 'end', true],
        ];
    }

    /**
     * @param int[] $date
     * @param 'end'|'none' $excludeBoundaries
     */
    #[DataProvider('endEqualsProvider')]
    public function testEndEquals(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->endEquals(DateTime::createFromArray($date)));
    }

    /**
     * @param int[] $date
     * @param 'end'|'none' $excludeBoundaries
     */
    #[DataProvider('endEqualsProvider')]
    public function testEndEqualsDate(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            Date::createFromArray([2022, 1, 1]),
            Date::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->endEquals(Date::createFromArray($date)));
    }
}

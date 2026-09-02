<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Date;

use Fyre\Utility\DateTime\Date;
use PHPUnit\Framework\Attributes\DataProvider;

trait ManipulateTestTrait
{
    /**
     * @return array<string, array{int[], string, int[], string}>
     */
    public static function manipulateProvider(): array
    {
        return [
            'add day' => [[2018], 'addDay', [], '2018-01-02'],
            'add days' => [[2018], 'addDays', [2], '2018-01-03'],
            'add month' => [[2018], 'addMonth', [], '2018-02-01'],
            'add months' => [[2018], 'addMonths', [2], '2018-03-01'],
            'add week' => [[2018], 'addWeek', [], '2018-01-08'],
            'add weeks' => [[2018], 'addWeeks', [2], '2018-01-15'],
            'add year' => [[2018], 'addYear', [], '2019-01-01'],
            'add years' => [[2018], 'addYears', [2], '2020-01-01'],
            'end of month' => [[2018, 6, 15], 'endOfMonth', [], '2018-06-30'],
            'end of quarter' => [[2018, 8, 15], 'endOfQuarter', [], '2018-09-30'],
            'end of week' => [[2018, 6, 15], 'endOfWeek', [], '2018-06-16'],
            'end of year' => [[2018, 6, 15], 'endOfYear', [], '2018-12-31'],
            'start of month' => [[2018, 6, 15], 'startOfMonth', [], '2018-06-01'],
            'start of quarter' => [[2018, 8, 15], 'startOfQuarter', [], '2018-07-01'],
            'start of week' => [[2018, 6, 15], 'startOfWeek', [], '2018-06-10'],
            'start of year' => [[2018, 6, 15], 'startOfYear', [], '2018-01-01'],
            'sub day' => [[2018], 'subDay', [], '2017-12-31'],
            'sub days' => [[2018], 'subDays', [2], '2017-12-30'],
            'sub month' => [[2018], 'subMonth', [], '2017-12-01'],
            'sub months' => [[2018], 'subMonths', [2], '2017-11-01'],
            'sub week' => [[2018], 'subWeek', [], '2017-12-25'],
            'sub weeks' => [[2018], 'subWeeks', [2], '2017-12-18'],
            'sub year' => [[2018], 'subYear', [], '2017-01-01'],
            'sub years' => [[2018], 'subYears', [2], '2016-01-01'],
        ];
    }

    /**
     * @param int[] $parts
     * @param int[] $arguments
     */
    #[DataProvider('manipulateProvider')]
    public function testManipulate(array $parts, string $method, array $arguments, string $expected): void
    {
        $date1 = Date::createFromArray($parts);

        /** @var Date $date2 */
        $date2 = $date1->$method(...$arguments);

        $this->assertNotSame(
            $date1,
            $date2
        );
        $this->assertSame(
            $expected,
            $date2->toIsoString()
        );
    }
}

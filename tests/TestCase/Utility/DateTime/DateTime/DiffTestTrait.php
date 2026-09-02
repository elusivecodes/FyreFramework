<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @phpstan-type DiffInMethod 'diffInDays'|'diffInHours'|'diffInMinutes'|'diffInMonths'|'diffInSeconds'|'diffInWeeks'|'diffInYears'
 */
trait DiffTestTrait
{
    /**
     * @return array<string, array{DiffInMethod, int[], int[], bool, int}>
     */
    public static function diffInProvider(): array
    {
        return [
            'days' => ['diffInDays', [2018, 8, 23], [2018, 6, 15], true, 69],
            'days negative' => ['diffInDays', [2018, 6, 15], [2018, 6, 23], true, -8],
            'days relative' => ['diffInDays', [2018, 1, 2, 0], [2018, 1, 1, 1], true, 1],
            'days exact' => ['diffInDays', [2018, 1, 2, 0], [2018, 1, 1, 1], false, 0],
            'hours' => ['diffInHours', [2018, 6, 18, 23], [2018, 6, 15, 12], true, 83],
            'hours negative' => ['diffInHours', [2018, 6, 15, 12], [2018, 6, 15, 23], true, -11],
            'hours relative' => ['diffInHours', [2018, 1, 1, 1, 0], [2018, 1, 1, 0, 1], true, 1],
            'hours exact' => ['diffInHours', [2018, 1, 1, 1, 0], [2018, 1, 1, 0, 1], false, 0],
            'minutes' => ['diffInMinutes', [2018, 6, 15, 16, 30], [2018, 6, 15, 12, 15], true, 255],
            'minutes negative' => ['diffInMinutes', [2018, 6, 15, 12, 15], [2018, 6, 15, 12, 30], true, -15],
            'minutes relative' => ['diffInMinutes', [2018, 1, 1, 0, 1, 0], [2018, 1, 1, 0, 0, 1], true, 1],
            'minutes exact' => ['diffInMinutes', [2018, 1, 1, 0, 1, 0], [2018, 1, 1, 0, 0, 1], false, 0],
            'months' => ['diffInMonths', [2018, 9], [2016, 6], true, 27],
            'months negative' => ['diffInMonths', [2018, 6], [2018, 9], true, -3],
            'months relative' => ['diffInMonths', [2018, 2, 1], [2018, 1, 2], true, 1],
            'months exact' => ['diffInMonths', [2018, 2, 1], [2018, 1, 2], false, 0],
            'seconds' => ['diffInSeconds', [2018, 6, 15, 12, 50, 30], [2018, 6, 15, 12, 30, 15], true, 1215],
            'seconds negative' => ['diffInSeconds', [2018, 6, 15, 12, 30, 15], [2018, 6, 15, 12, 30, 30], true, -15],
            'seconds relative' => ['diffInSeconds', [2018, 1, 1, 0, 0, 1, 0], [2018, 1, 1, 0, 0, 0, 1], true, 1],
            'seconds exact' => ['diffInSeconds', [2018, 1, 1, 0, 0, 1, 0], [2018, 1, 1, 0, 0, 0, 1], false, 0],
            'weeks' => ['diffInWeeks', [2018, 8, 23], [2018, 6, 15], true, 10],
            'weeks negative' => ['diffInWeeks', [2018, 5, 15], [2018, 6, 23], true, -5],
            'weeks relative' => ['diffInWeeks', [2018, 1, 8], [2018, 1, 2], true, 1],
            'weeks exact' => ['diffInWeeks', [2018, 1, 8], [2018, 1, 2], false, 0],
            'years' => ['diffInYears', [2018], [2016], true, 2],
            'years negative' => ['diffInYears', [2016], [2018], true, -2],
            'years relative' => ['diffInYears', [2018, 1], [2017, 2], true, 1],
            'years exact' => ['diffInYears', [2018, 1], [2017, 2], false, 0],
        ];
    }

    public function testDiff(): void
    {
        $this->assertSame(
            54391815150,
            DateTime::createFromArray([2018, 6, 15, 12, 30, 30, 500])
                ->diff(
                    DateTime::createFromArray([2016, 9, 23, 23, 40, 15, 350])
                )
        );
    }

    /**
     * @param DiffInMethod $method
     * @param int[] $dateTime1
     * @param int[] $dateTime2
     */
    #[DataProvider('diffInProvider')]
    public function testDiffIn(
        string $method,
        array $dateTime1,
        array $dateTime2,
        bool $relative,
        int $expected
    ): void {
        $this->assertSame(
            $expected,
            DateTime::createFromArray($dateTime1)->$method(
                DateTime::createFromArray($dateTime2),
                $relative
            )
        );
    }
}

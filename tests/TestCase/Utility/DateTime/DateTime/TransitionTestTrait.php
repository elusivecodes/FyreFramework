<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;

trait TransitionTestTrait
{
    public function testDstPostTransition(): void
    {
        $date1 = DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss ZZZZZ', '07/04/2019 03:01:00 +11:00');
        $date2 = $date1->withTimeZone('Australia/Sydney');

        $this->assertSame(
            'Sun Apr 07 2019 02:01:00 +1000 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstPostTransitionArray(): void
    {
        $date1 = DateTime::createFromArray([2019, 4, 7, 3, 1, 0, 0], '+11:00');
        $date2 = $date1->withTimeZone('Australia/Sydney');

        $this->assertSame(
            'Sun Apr 07 2019 02:01:00 +1000 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstPreTransition(): void
    {
        $date1 = DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss ZZZZZ', '07/04/2019 02:01:00 +11:00');
        $date2 = $date1->withTimeZone('Australia/Sydney');

        $this->assertSame(
            'Sun Apr 07 2019 02:01:00 +1100 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstPreTransitionArray(): void
    {
        $date1 = DateTime::createFromArray([2019, 4, 7, 2, 1, 0, 0], '+11:00');
        $date2 = $date1->withTimeZone('Australia/Sydney');

        $this->assertSame(
            'Sun Apr 07 2019 02:01:00 +1100 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstTransitionAddDay(): void
    {
        $date1 = DateTime::createFromArray([2023, 9, 30, 23, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->addDay();

        $this->assertSame(
            'Sat Sep 30 2023 23:00:00 +1000 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Sun Oct 01 2023 23:00:00 +1100 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstTransitionAddHour(): void
    {
        $date1 = DateTime::createFromArray([2023, 10, 1, 1, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->addHour();

        $this->assertSame(
            'Sun Oct 01 2023 01:00:00 +1000 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Sun Oct 01 2023 03:00:00 +1100 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstTransitionAddHourReverse(): void
    {
        $date1 = DateTime::createFromArray([2023, 4, 2, 1, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->addHour();
        $date3 = $date2->addHour();

        $this->assertSame(
            'Sun Apr 02 2023 01:00:00 +1100 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Sun Apr 02 2023 02:00:00 +1100 (Australia/Sydney)',
            $date2->toString()
        );

        $this->assertSame(
            'Sun Apr 02 2023 02:00:00 +1000 (Australia/Sydney)',
            $date3->toString()
        );
    }

    public function testDstTransitionAddMonth(): void
    {
        $date1 = DateTime::createFromArray([2023, 9, 30, 23, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->addMonth();

        $this->assertSame(
            'Sat Sep 30 2023 23:00:00 +1000 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Mon Oct 30 2023 23:00:00 +1100 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstTransitionAddYear(): void
    {
        $date1 = DateTime::createFromArray([2023, 10, 1, 3, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->addYear();

        $this->assertSame(
            'Sun Oct 01 2023 03:00:00 +1100 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Tue Oct 01 2024 03:00:00 +1000 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstTransitionBackward(): void
    {
        $date = DateTime::createFromArray([2023, 4, 2, 2, 0, 0, 0], 'Australia/Sydney');

        $this->assertSame(
            'Sun Apr 02 2023 02:00:00 +1000 (Australia/Sydney)',
            $date->toString()
        );
    }

    public function testDstTransitionForward(): void
    {
        $date = DateTime::createFromArray([2023, 10, 1, 2, 0, 0, 0], 'Australia/Sydney');

        $this->assertSame(
            'Sun Oct 01 2023 03:00:00 +1100 (Australia/Sydney)',
            $date->toString()
        );
    }

    public function testDstTransitionFromDate(): void
    {
        $date1 = DateTime::createFromArray([2023, 10, 2, 0, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->withMonth(9, 30);

        $this->assertSame(
            'Mon Oct 02 2023 00:00:00 +1100 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Sat Sep 30 2023 00:00:00 +1000 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstTransitionFromHour(): void
    {
        $date1 = DateTime::createFromArray([2023, 10, 1, 3, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->withHours(1);

        $this->assertSame(
            'Sun Oct 01 2023 03:00:00 +1100 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Sun Oct 01 2023 01:00:00 +1000 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstTransitionFromMonth(): void
    {
        $date1 = DateTime::createFromArray([2023, 10, 2, 0, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->withMonth(9);

        $this->assertSame(
            'Mon Oct 02 2023 00:00:00 +1100 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Sat Sep 02 2023 00:00:00 +1000 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstTransitionFromYear(): void
    {
        $date1 = DateTime::createFromArray([2024, 10, 1, 3, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->withYear(2023);

        $this->assertSame(
            'Tue Oct 01 2024 03:00:00 +1000 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Sun Oct 01 2023 03:00:00 +1100 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstTransitionSubtractDay(): void
    {
        $date1 = DateTime::createFromArray([2023, 10, 2, 0, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->subDay();

        $this->assertSame(
            'Mon Oct 02 2023 00:00:00 +1100 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Sun Oct 01 2023 00:00:00 +1000 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstTransitionSubtractHour(): void
    {
        $date1 = DateTime::createFromArray([2023, 10, 1, 3, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->subHour();

        $this->assertSame(
            'Sun Oct 01 2023 03:00:00 +1100 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Sun Oct 01 2023 01:00:00 +1000 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstTransitionSubtractHourReverse(): void
    {
        $date1 = DateTime::createFromArray([2023, 4, 2, 3, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->subHour();
        $date3 = $date2->subHour();

        $this->assertSame(
            'Sun Apr 02 2023 03:00:00 +1000 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Sun Apr 02 2023 02:00:00 +1000 (Australia/Sydney)',
            $date2->toString()
        );

        $this->assertSame(
            'Sun Apr 02 2023 02:00:00 +1100 (Australia/Sydney)',
            $date3->toString()
        );
    }

    public function testDstTransitionSubtractMonth(): void
    {
        $date1 = DateTime::createFromArray([2023, 10, 2, 0, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->subMonth();

        $this->assertSame(
            'Mon Oct 02 2023 00:00:00 +1100 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Sat Sep 02 2023 00:00:00 +1000 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstTransitionSubtractYear(): void
    {
        $date1 = DateTime::createFromArray([2024, 10, 1, 3, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->subYear();

        $this->assertSame(
            'Tue Oct 01 2024 03:00:00 +1000 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Sun Oct 01 2023 03:00:00 +1100 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstTransitionToDate(): void
    {
        $date1 = DateTime::createFromArray([2023, 9, 30, 23, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->withMonth(10, 1);

        $this->assertSame(
            'Sat Sep 30 2023 23:00:00 +1000 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Sun Oct 01 2023 23:00:00 +1100 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstTransitionToHour(): void
    {
        $date1 = DateTime::createFromArray([2023, 10, 1, 1, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->withHours(3);

        $this->assertSame(
            'Sun Oct 01 2023 01:00:00 +1000 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Sun Oct 01 2023 03:00:00 +1100 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstTransitionToMonth(): void
    {
        $date1 = DateTime::createFromArray([2023, 9, 30, 23, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->withMonth(10);

        $this->assertSame(
            'Sat Sep 30 2023 23:00:00 +1000 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Mon Oct 30 2023 23:00:00 +1100 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testDstTransitionToYear(): void
    {
        $date1 = DateTime::createFromArray([2023, 10, 1, 3, 0, 0, 0], 'Australia/Sydney');
        $date2 = $date1->withYear(2024);

        $this->assertSame(
            'Sun Oct 01 2023 03:00:00 +1100 (Australia/Sydney)',
            $date1->toString()
        );

        $this->assertSame(
            'Tue Oct 01 2024 03:00:00 +1000 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testNonDstPostTransition(): void
    {
        $date1 = DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss ZZZZZ', '07/04/2019 03:01:00 +10:00');
        $date2 = $date1->withTimeZone('Australia/Sydney');

        $this->assertSame(
            'Sun Apr 07 2019 03:01:00 +1000 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testNonDstPostTransitionArray(): void
    {
        $date1 = DateTime::createFromArray([2019, 4, 7, 3, 1, 0, 0], '+10:00');
        $date2 = $date1->withTimeZone('Australia/Sydney');

        $this->assertSame(
            'Sun Apr 07 2019 03:01:00 +1000 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testNonDstPreTransition(): void
    {
        $date1 = DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss ZZZZZ', '07/04/2019 02:01:00 +10:00');
        $date2 = $date1->withTimeZone('Australia/Sydney');

        $this->assertSame(
            'Sun Apr 07 2019 02:01:00 +1000 (Australia/Sydney)',
            $date2->toString()
        );
    }

    public function testNonDstPreTransitionArray(): void
    {
        $date1 = DateTime::createFromArray([2019, 4, 7, 2, 1, 0, 0], '+10:00');
        $date2 = $date1->withTimeZone('Australia/Sydney');

        $this->assertSame(
            'Sun Apr 07 2019 02:01:00 +1000 (Australia/Sydney)',
            $date2->toString()
        );
    }
}

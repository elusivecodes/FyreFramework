<?php
declare(strict_types=1);

namespace Tests\Mock\Jobs;

use RuntimeException;

use function file_put_contents;

use const FILE_APPEND;

class MockJob
{
    public function error(): void
    {
        throw new RuntimeException();
    }

    public function fail(): false
    {
        return false;
    }

    public function run(int $test): void
    {
        file_put_contents('tmp/job', (string) $test, FILE_APPEND);
    }
}

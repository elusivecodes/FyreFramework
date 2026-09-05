<?php
declare(strict_types=1);

namespace Tests\Mock\Jobs;

use PDO;
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

    public function pdoError(): void
    {
        new PDO('sqlite::memory:')->exec('SELECT * FROM missing_table');
    }

    public function run(int $test): void
    {
        file_put_contents('tmp/job', (string) $test, FILE_APPEND);
    }
}

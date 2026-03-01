<?php
declare(strict_types=1);

namespace Tests\Mock\Commands;

use Fyre\Console\Command;
use Override;

class TestCommand extends Command
{
    #[Override]
    protected string|null $alias = 'tester';

    #[Override]
    protected string $description = 'This is a test command.';

    public function run(): void {}
}

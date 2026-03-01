<?php
declare(strict_types=1);

namespace Tests\Mock\Commands;

use Fyre\Console\Command;
use Fyre\Utility\DateTime\DateTime;
use Override;

class TypeOptionsCommand extends Command
{
    #[Override]
    protected array $options = [
        'test' => [
            'text' => 'What is the date?',
            'as' => 'date',
            'required' => true,
        ],
    ];

    public function run(DateTime $test): int
    {
        return $test->isSameDay(DateTime::now()) ?
            static::CODE_SUCCESS :
            static::CODE_ERROR;
    }
}

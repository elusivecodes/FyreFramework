<?php
declare(strict_types=1);

namespace Tests\Mock\DB\Traits;

use Override;

use function str_replace;

trait TestConnectionTrait
{
    #[Override]
    public function connect(): void {}

    #[Override]
    public function quote(string $value): string
    {
        return '\''.str_replace('\'', '\'\'', $value).'\'';
    }
}

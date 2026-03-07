<?php
declare(strict_types=1);

namespace Tests\Mock\Enums;

use Fyre\Utility\EnumLabelInterface;

enum Status: string implements EnumLabelInterface
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft label',
            self::Published => 'Published label',
        };
    }
}

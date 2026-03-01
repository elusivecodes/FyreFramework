<?php
declare(strict_types=1);

namespace Tests\Mock\Entities;

use Fyre\ORM\Entity;

use function floor;
use function number_format;

class MockEntity extends Entity
{
    protected function _getDecimal($value): string
    {
        return number_format($value ?? 0, 2);
    }

    protected function _getNumber(): float
    {
        return $this->get('integer');
    }

    protected function _setInteger($value): float
    {
        return floor($value);
    }
}

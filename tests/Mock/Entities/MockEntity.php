<?php
declare(strict_types=1);

namespace Tests\Mock\Entities;

use Fyre\ORM\Entity;

use function floor;
use function number_format;

/**
 * @property string $decimal
 * @property float $number
 * @property float $integer
 */
class MockEntity extends Entity
{
    protected function _getDecimal(float $value): string
    {
        return number_format($value, 2);
    }

    protected function _getNumber(): float
    {
        return $this->get('integer');
    }

    protected function _setInteger(float $value): float
    {
        return floor($value);
    }
}

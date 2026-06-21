<?php
declare(strict_types=1);

namespace Tests\Mock\Entities;

use Fyre\ORM\Entity;

/**
 * @property int|null $missing
 * @property int|string $test
 */
class MagicEntity extends Entity {}

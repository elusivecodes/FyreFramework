<?php
declare(strict_types=1);

namespace Tests\Mock\Entities;

use Fyre\ORM\Entity;

/**
 * @property int|null $id
 * @property string $name
 * @property Child[] $children
 */
class Item extends Entity {}

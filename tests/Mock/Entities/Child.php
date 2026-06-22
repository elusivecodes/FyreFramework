<?php
declare(strict_types=1);

namespace Tests\Mock\Entities;

use Fyre\ORM\Entity;

/**
 * @property int|null $id
 * @property int|null $context_id
 * @property int|null $item_id
 * @property string|null $value
 * @property Item $item
 */
class Child extends Entity {}

<?php
declare(strict_types=1);

namespace Tests\Mock\Entities;

use Fyre\ORM\Entity;

/**
 * @property int|null $id
 * @property string|null $tag
 * @property PostsTag $_joinData
 */
class Tag extends Entity {}

<?php
declare(strict_types=1);

namespace Tests\Mock\Entities;

use Fyre\ORM\Entity;

/**
 * @property int|null $id
 * @property int $post_id
 * @property int $tag_id
 * @property int|null $value
 */
class PostsTag extends Entity {}

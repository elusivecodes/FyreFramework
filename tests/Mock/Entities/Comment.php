<?php
declare(strict_types=1);

namespace Tests\Mock\Entities;

use Fyre\ORM\Entity;
use Fyre\Utility\DateTime\DateTime;

/**
 * @property int|null $id
 * @property int|null $user_id
 * @property int|null $post_id
 * @property string|null $comment
 * @property DateTime|null $deleted
 * @property User $user
 * @property Post $post
 */
class Comment extends Entity {}

<?php
declare(strict_types=1);

namespace Tests\Mock\Entities;

use Fyre\ORM\Entity;
use Fyre\Utility\DateTime\DateTime;

/**
 * @property int|null $id
 * @property int|null $user_id
 * @property string|null $title
 * @property string|null $content
 * @property DateTime|null $deleted
 * @property User $user
 * @property Comment[] $comments
 * @property Tag[] $tags
 */
class Post extends Entity {}

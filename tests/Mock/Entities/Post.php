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
 * @property User|null $user
 * @property Comment[]|null $comments
 * @property Tag[]|null $tags
 */
class Post extends Entity {}

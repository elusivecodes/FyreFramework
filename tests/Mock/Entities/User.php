<?php
declare(strict_types=1);

namespace Tests\Mock\Entities;

use Fyre\ORM\Entity;
use Fyre\Utility\DateTime\DateTime;

/**
 * @property int|null $id
 * @property string|null $username
 * @property string|null $email
 * @property string|null $password
 * @property string|null $token
 * @property string|null $name
 * @property DateTime|null $deleted
 * @property Address $address
 * @property Comment[] $comments
 * @property array<string, Entity> $_matchingData
 * @property Post[] $posts
 */
class User extends Entity {}

<?php
declare(strict_types=1);

namespace Tests\Mock\Entities;

use Fyre\ORM\Entity;
use Fyre\Utility\DateTime\DateTime;

/**
 * @property int|null $id
 * @property int|null $user_id
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $suburb
 * @property string|null $state
 * @property DateTime|null $deleted
 * @property User|null $user
 */
class Address extends Entity {}

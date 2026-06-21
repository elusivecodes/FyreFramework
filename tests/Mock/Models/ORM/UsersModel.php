<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM;

use Fyre\Form\Rule;
use Fyre\Form\Validator;
use Fyre\ORM\Attributes\HasMany;
use Fyre\ORM\Attributes\HasOne;
use Fyre\ORM\Entity;
use Fyre\ORM\Model;
use Fyre\ORM\Relationships\HasMany as HasManyRelationship;
use Fyre\ORM\Relationships\HasOne as HasOneRelationship;
use Fyre\ORM\RuleSet;
use Override;
use Tests\Mock\Entities\User;
use Tests\Mock\Models\Traits\TestTrait;

/**
 * @extends Model<User>
 *
 * @property HasOneRelationship<static, AddressesModel> $Addresses
 * @property HasManyRelationship<static, CommentsModel> $Comments
 * @property HasManyRelationship<static, PostsModel> $Posts
 */
#[HasOne('Addresses')]
#[HasMany('Comments')]
#[HasMany('Posts', [
    'saveStrategy' => 'replace',
    'dependent' => true,
])]
class UsersModel extends Model
{
    use TestTrait;

    #[Override]
    public function buildRules(RuleSet $rules): RuleSet
    {
        $rules->add(static function(Entity $entity) {
            if ($entity->get('name') === 'failRules') {
                return false;
            }
        });

        return $rules;
    }

    #[Override]
    public function buildValidator(Validator $validator): Validator
    {
        $validator->add('name', Rule::required(), on: 'create');

        return $validator;
    }
}

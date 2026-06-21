<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM;

use Fyre\Form\Rule;
use Fyre\Form\Validator;
use Fyre\ORM\Attributes\BelongsTo;
use Fyre\ORM\Entity;
use Fyre\ORM\Model;
use Fyre\ORM\Relationships\BelongsTo as BelongsToRelationship;
use Fyre\ORM\RuleSet;
use Override;
use Tests\Mock\Entities\Comment;

/**
 * @extends Model<Comment>
 *
 * @property BelongsToRelationship<static, PostsModel> $Posts
 * @property BelongsToRelationship<static, UsersModel> $Users
 */
#[BelongsTo('Posts')]
#[BelongsTo('Users')]
class CommentsModel extends Model
{
    #[Override]
    public function buildRules(RuleSet $rules): RuleSet
    {
        $rules->add(static function(Entity $entity) {
            if ($entity->get('content') === 'failRules') {
                return false;
            }
        });

        return $rules;
    }

    #[Override]
    public function buildValidator(Validator $validator): Validator
    {
        $validator->add('content', Rule::required(), on: 'create');

        return $validator;
    }
}

<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM;

use Fyre\Form\Rule;
use Fyre\Form\Validator;
use Fyre\ORM\Attributes\HasMany;
use Fyre\ORM\Attributes\HasOne;
use Fyre\ORM\Entity;
use Fyre\ORM\Model;
use Fyre\ORM\RuleSet;
use Override;
use Tests\Mock\Models\ORM\Traits\TestTrait;

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
    public function buildValidation(Validator $validator): Validator
    {
        $validator->add('name', Rule::required(), on: 'create');

        return $validator;
    }
}

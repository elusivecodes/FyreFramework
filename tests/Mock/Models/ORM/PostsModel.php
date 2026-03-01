<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM;

use Fyre\Form\Rule;
use Fyre\Form\Validator;
use Fyre\ORM\Attributes\BelongsTo;
use Fyre\ORM\Attributes\HasMany;
use Fyre\ORM\Attributes\ManyToMany;
use Fyre\ORM\Entity;
use Fyre\ORM\Model;
use Fyre\ORM\RuleSet;
use Override;
use Tests\Mock\Models\ORM\Traits\TestTrait;

#[BelongsTo('Users')]
#[HasMany('Comments', [
    'saveStrategy' => 'replace',
])]
#[ManyToMany('Tags')]
class PostsModel extends Model
{
    use TestTrait;

    #[Override]
    public function buildRules(RuleSet $rules): RuleSet
    {
        $rules->add(static function(Entity $entity) {
            if ($entity->get('title') === 'failRules') {
                return false;
            }
        });

        return $rules;
    }

    #[Override]
    public function buildValidation(Validator $validator): Validator
    {
        $validator->add('title', Rule::required(), on: 'create');

        return $validator;
    }

    #[Override]
    public function initialize(): void
    {
        $this->testField = 'title';
    }
}

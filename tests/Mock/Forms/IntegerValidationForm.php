<?php
declare(strict_types=1);

namespace Tests\Mock\Forms;

use Fyre\Form\Form;
use Fyre\Form\Rule;
use Fyre\Form\Schema;
use Fyre\Form\Validator;
use Override;

class IntegerValidationForm extends Form
{
    #[Override]
    public function buildSchema(Schema $schema): Schema
    {
        return $schema
            ->addField('user_id', ['type' => 'integer']);
    }

    #[Override]
    public function buildValidator(Validator $validator): Validator
    {
        return $validator
            ->add('user_id', Rule::exactLength(2));
    }
}

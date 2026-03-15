<?php
declare(strict_types=1);

namespace Tests\Mock\Forms;

use Fyre\Form\Form;
use Fyre\Form\Rule;
use Fyre\Form\Schema;
use Fyre\Form\Validator;
use Override;

class DateValidationForm extends Form
{
    #[Override]
    public function buildSchema(Schema $schema): Schema
    {
        return $schema
            ->addField('start', ['type' => 'date']);
    }

    #[Override]
    public function buildValidator(Validator $validator): Validator
    {
        return $validator
            ->add('start', Rule::exactLength(10));
    }
}

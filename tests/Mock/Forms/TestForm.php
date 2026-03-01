<?php
declare(strict_types=1);

namespace Tests\Mock\Forms;

use Fyre\Form\Form;
use Fyre\Form\Rule;
use Fyre\Form\Schema;
use Fyre\Form\Validator;
use Override;

class TestForm extends Form
{
    #[Override]
    public function buildSchema(Schema $schema): Schema
    {
        return $schema
            ->addField('title')
            ->addField('user_id', [
                'type' => 'integer',
            ])
            ->addField('value', [
                'type' => 'decimal',
                'precision' => 2,
            ])
            ->addField('start', [
                'type' => 'date',
            ])
            ->addField('bool', [
                'type' => 'boolean',
                'default' => true,
            ]);
    }

    #[Override]
    public function buildValidation(Validator $validator): Validator
    {
        $validator->add('title', Rule::required());
        $validator->add('title', Rule::minLength(10));

        $validator->add('user_id', Rule::required());
        $validator->add('user_id', Rule::integer());

        $validator->add('value', Rule::decimal());

        $validator->add('start', Rule::required());
        $validator->add('start', Rule::date());

        $validator->add('bool', Rule::boolean());

        return $validator;
    }

    #[Override]
    protected function process(array $data): bool
    {
        return true;
    }
}

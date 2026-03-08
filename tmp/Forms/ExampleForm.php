<?php
declare(strict_types=1);

namespace Example\Forms;

use Fyre\Form\Form;
use Fyre\Form\Schema;
use Fyre\Form\Validator;
use Override;

class ExampleForm extends Form
{
    #[Override]
    public function buildSchema(Schema $schema): Schema
    {
        return $schema;
    }

    #[Override]
    public function buildValidator(Validator $validator): Validator
    {
        return $validator;
    }

    #[Override]
    protected function process(array $data): bool
    {
        return true;
    }
}

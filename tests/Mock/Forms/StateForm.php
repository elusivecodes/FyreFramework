<?php
declare(strict_types=1);

namespace Tests\Mock\Forms;

use Fyre\Form\Form;
use Fyre\Form\Schema;
use Override;
use Tests\Mock\Enums\State;

class StateForm extends Form
{
    #[Override]
    public function buildSchema(Schema $schema): Schema
    {
        return $schema
            ->addField('status', ['type' => 'string'])
            ->setEnumClass('status', State::class);
    }
}

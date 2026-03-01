<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Sqlite;

use Fyre\Form\Rule;
use Fyre\Utility\DateTime\DateTime;

trait TimeTestTrait
{
    public function testTimeEntityValue(): void
    {
        $this->db->query(<<<'EOT'
            CREATE TABLE contexts (
                id INTEGER NOT NULL,
                value TIME NULL DEFAULT NULL,
                PRIMARY KEY (id)
            )
        EOT);

        $entity = $this->model->newEntity([
            'value' => DateTime::createFromArray([2022, 1, 1, 12, 30]),
        ]);

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="time" value="12:30" />',
            $this->view->Form->input('value')
        );
    }

    public function testTimeRequiredValidation(): void
    {
        $this->db->query(<<<'EOT'
            CREATE TABLE contexts (
                id INTEGER NOT NULL,
                value TIME NULL DEFAULT NULL,
                PRIMARY KEY (id)
            )
        EOT);

        $this->validator->add('value', Rule::required());

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="time" required />',
            $this->view->Form->input('value')
        );
    }

    public function testTimeSchema(): void
    {
        $this->db->query(<<<'EOT'
            CREATE TABLE contexts (
                id INTEGER NOT NULL,
                value TIME NULL DEFAULT NULL,
                PRIMARY KEY (id)
            )
        EOT);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="time" />',
            $this->view->Form->input('value')
        );
    }

    public function testTimeSchemaDefaultValue(): void
    {
        $this->db->query(<<<'EOT'
            CREATE TABLE contexts (
                id INTEGER NOT NULL,
                value TIME NOT NULL DEFAULT '12:30:00',
                PRIMARY KEY (id)
            )
        EOT);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="time" value="12:30" />',
            $this->view->Form->input('value')
        );
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Mysql;

use Tests\Mock\Enums\Status;

trait EnumClassTestTrait
{
    public function testEnumClassFieldOptions(): void
    {
        $this->db->query(<<<'EOT'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        EOT);

        $this->model->getSchema()->setEnumClass('value', Status::class);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<select id="value" name="value"><option value="draft">Draft label</option><option value="published">Published label</option></select>',
            $this->view->Form->input('value')
        );
    }

    public function testEnumClassFieldSelectedValue(): void
    {
        $this->db->query(<<<'EOT'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        EOT);

        $this->model->getSchema()->setEnumClass('value', Status::class);

        $entity = $this->model->newEntity([
            'value' => 'published',
        ]);

        $this->view->Form->open($entity);

        $this->assertSame(
            '<select id="value" name="value"><option value="draft">Draft label</option><option value="published" selected>Published label</option></select>',
            $this->view->Form->input('value')
        );
    }
}

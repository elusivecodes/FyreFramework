<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\MariaDb;

use Fyre\Form\Rule;

trait BlobTestTrait
{
    public function testBlobRequiredValidation(): void
    {
        $this->db->query(<<<'EOT'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value BLOB NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        EOT);

        $this->validator->add('value', Rule::required());

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="file" required />',
            $this->view->Form->input('value')
        );
    }
}

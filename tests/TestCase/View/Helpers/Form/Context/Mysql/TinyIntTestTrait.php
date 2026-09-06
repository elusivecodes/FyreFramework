<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Mysql;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait TinyIntTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function tinyIntLowerValidationBoundProvider(): array
    {
        return [
            'inclusive' => [
                'greaterThanOrEquals',
                '<input id="value" name="value" type="number" placeholder="Value" min="100" max="127" step="1" />',
            ],
            'exclusive' => [
                'greaterThan',
                '<input id="value" name="value" type="number" placeholder="Value" min="101" max="127" step="1" />',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function tinyIntUpperValidationBoundProvider(): array
    {
        return [
            'inclusive' => [
                'lessThanOrEquals',
                '<input id="value" name="value" type="number" placeholder="Value" min="-128" max="100" step="1" />',
            ],
            'exclusive' => [
                'lessThan',
                '<input id="value" name="value" type="number" placeholder="Value" min="-128" max="99" step="1" />',
            ],
        ];
    }

    public function testTinyInt1EntityValue(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value TINYINT(1) NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEntity([
            'value' => true,
        ]);

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input name="value" type="hidden" value="0" /><input id="value" name="value" type="checkbox" value="1" checked />',
            $this->view->Form->input('value')
        );
    }

    public function testTinyInt1RequiredValidation(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value TINYINT(1) NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $this->validator->add('value', Rule::required());

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input name="value" type="hidden" value="0" /><input id="value" name="value" type="checkbox" value="1" required />',
            $this->view->Form->input('value')
        );
    }

    public function testTinyInt1Schema(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value TINYINT(1) NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input name="value" type="hidden" value="0" /><input id="value" name="value" type="checkbox" value="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testTinyInt1SchemaDefaultValue(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input name="value" type="hidden" value="0" /><input id="value" name="value" type="checkbox" value="1" checked />',
            $this->view->Form->input('value')
        );
    }

    public function testTinyIntBetweenValidation(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value TINYINT NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $this->validator->add('value', Rule::between(10, 100));

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="10" max="100" step="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testTinyIntEntityValue(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value TINYINT NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEntity([
            'value' => 99,
        ]);

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="99" placeholder="Value" min="-128" max="127" step="1" />',
            $this->view->Form->input('value')
        );
    }

    #[DataProvider('tinyIntLowerValidationBoundProvider')]
    public function testTinyIntLowerValidationBound(string $rule, string $expected): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value TINYINT NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $this->validator->add('value', Rule::$rule(100));

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            $expected,
            $this->view->Form->input('value')
        );
    }

    public function testTinyIntMinMaxSchema(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value TINYINT NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="-128" max="127" step="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testTinyIntRequiredValidation(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value TINYINT NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $this->validator->add('value', Rule::required());

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="-128" max="127" step="1" required />',
            $this->view->Form->input('value')
        );
    }

    public function testTinyIntSchemaDefaultValue(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value TINYINT NOT NULL DEFAULT 99,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="99" placeholder="Value" min="-128" max="127" step="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testTinyIntUnsignedMinMaxSchema(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value TINYINT UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="0" max="255" step="1" />',
            $this->view->Form->input('value')
        );
    }

    #[DataProvider('tinyIntUpperValidationBoundProvider')]
    public function testTinyIntUpperValidationBound(string $rule, string $expected): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value TINYINT NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $this->validator->add('value', Rule::$rule(100));

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            $expected,
            $this->view->Form->input('value')
        );
    }
}

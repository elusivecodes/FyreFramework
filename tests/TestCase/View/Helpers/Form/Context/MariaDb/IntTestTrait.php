<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\MariaDb;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait IntTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function intLowerValidationBoundProvider(): array
    {
        return [
            'inclusive' => [
                'greaterThanOrEquals',
                '<input id="value" name="value" type="number" placeholder="Value" min="100" max="2147483647" step="1" />',
            ],
            'exclusive' => [
                'greaterThan',
                '<input id="value" name="value" type="number" placeholder="Value" min="101" max="2147483647" step="1" />',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function intUpperValidationBoundProvider(): array
    {
        return [
            'inclusive' => [
                'lessThanOrEquals',
                '<input id="value" name="value" type="number" placeholder="Value" min="-2147483648" max="1000" step="1" />',
            ],
            'exclusive' => [
                'lessThan',
                '<input id="value" name="value" type="number" placeholder="Value" min="-2147483648" max="999" step="1" />',
            ],
        ];
    }

    public function testIntBetweenValidation(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value INT NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $this->validator->add('value', Rule::between(100, 1000));

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="100" max="1000" step="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testIntEntityValue(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value INT NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEntity([
            'value' => 999,
        ]);

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="999" placeholder="Value" min="-2147483648" max="2147483647" step="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testIntExistingEntityDefaultValue(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value INT NOT NULL DEFAULT 999,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEmptyEntity()
            ->setNew(false);

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="-2147483648" max="2147483647" step="1" />',
            $this->view->Form->input('value')
        );
    }

    #[DataProvider('intLowerValidationBoundProvider')]
    public function testIntLowerValidationBound(string $rule, string $expected): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value INT NULL DEFAULT NULL,
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

    public function testIntMinMaxSchema(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value INT NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="-2147483648" max="2147483647" step="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testIntRequiredValidation(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value INT NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $this->validator->add('value', Rule::required());

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="-2147483648" max="2147483647" step="1" required />',
            $this->view->Form->input('value')
        );
    }

    public function testIntSchemaDefaultValue(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value INT NOT NULL DEFAULT 999,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="999" placeholder="Value" min="-2147483648" max="2147483647" step="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testIntUnsignedMinMaxSchema(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value INT UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="0" max="4294967295" step="1" />',
            $this->view->Form->input('value')
        );
    }

    #[DataProvider('intUpperValidationBoundProvider')]
    public function testIntUpperValidationBound(string $rule, string $expected): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value INT NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $this->validator->add('value', Rule::$rule(1000));

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            $expected,
            $this->view->Form->input('value')
        );
    }
}

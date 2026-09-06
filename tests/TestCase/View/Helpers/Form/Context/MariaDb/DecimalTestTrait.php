<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\MariaDb;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait DecimalTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function decimalLowerValidationBoundProvider(): array
    {
        return [
            'inclusive' => [
                'greaterThanOrEquals',
                '<input id="value" name="value" type="number" placeholder="Value" min="100" max="99999999.99" step="0.01" />',
            ],
            'exclusive' => [
                'greaterThan',
                '<input id="value" name="value" type="number" placeholder="Value" min="101" max="99999999.99" step="0.01" />',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function decimalPrecisionScaleProvider(): array
    {
        return [
            'precision equals scale' => [
                'DECIMAL(2,2) NULL DEFAULT NULL',
                '<input id="value" name="value" type="number" placeholder="Value" min="-0.99" max="0.99" step="0.01" />',
            ],
            'precision overflow' => [
                'DECIMAL(30,0) NULL DEFAULT NULL',
                '<input id="value" name="value" type="number" placeholder="Value" step="1" />',
            ],
            'zero scale' => [
                'DECIMAL(10,0) NULL DEFAULT NULL',
                '<input id="value" name="value" type="number" placeholder="Value" min="-9999999999" max="9999999999" step="1" />',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function decimalUpperValidationBoundProvider(): array
    {
        return [
            'inclusive' => [
                'lessThanOrEquals',
                '<input id="value" name="value" type="number" placeholder="Value" min="-99999999.99" max="1000" step="0.01" />',
            ],
            'exclusive' => [
                'lessThan',
                '<input id="value" name="value" type="number" placeholder="Value" min="-99999999.99" max="999" step="0.01" />',
            ],
        ];
    }

    public function testDecimalBetweenValidation(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value DECIMAL(10,2) NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $this->validator->add('value', Rule::between(100, 1000));

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="100" max="1000" step="0.01" />',
            $this->view->Form->input('value')
        );
    }

    public function testDecimalEntityValue(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value DECIMAL(10,2) NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEntity([
            'value' => 100.99,
        ]);

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="100.99" placeholder="Value" min="-99999999.99" max="99999999.99" step="0.01" />',
            $this->view->Form->input('value')
        );
    }

    #[DataProvider('decimalLowerValidationBoundProvider')]
    public function testDecimalLowerValidationBound(string $rule, string $expected): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value DECIMAL(10,2) NULL DEFAULT NULL,
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

    public function testDecimalMinMaxSchema(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value DECIMAL(10,2) NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="-99999999.99" max="99999999.99" step="0.01" />',
            $this->view->Form->input('value')
        );
    }

    #[DataProvider('decimalPrecisionScaleProvider')]
    public function testDecimalPrecisionScale(string $definition, string $expected): void
    {
        $this->db->query(<<<SQL
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value $definition,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            $expected,
            $this->view->Form->input('value')
        );
    }

    public function testDecimalRequiredValidation(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value DECIMAL(10,2) NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $this->validator->add('value', Rule::required());

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="-99999999.99" max="99999999.99" step="0.01" required />',
            $this->view->Form->input('value')
        );
    }

    public function testDecimalSchemaDefaultValue(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value DECIMAL(10,2) NOT NULL DEFAULT 100.99,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="100.99" placeholder="Value" min="-99999999.99" max="99999999.99" step="0.01" />',
            $this->view->Form->input('value')
        );
    }

    public function testDecimalUnsignedMinMaxSchema(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value DECIMAL(10,2) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="0" max="99999999.99" step="0.01" />',
            $this->view->Form->input('value')
        );
    }

    #[DataProvider('decimalUpperValidationBoundProvider')]
    public function testDecimalUpperValidationBound(string $rule, string $expected): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                value DECIMAL(10,2) NULL DEFAULT NULL,
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

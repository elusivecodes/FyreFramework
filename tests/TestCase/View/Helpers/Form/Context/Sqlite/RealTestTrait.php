<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Sqlite;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait RealTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function realLowerValidationBoundProvider(): array
    {
        return [
            'inclusive' => [
                'greaterThanOrEquals',
                '<input id="value" name="value" type="number" placeholder="Value" min="100" step="any" />',
            ],
            'exclusive' => [
                'greaterThan',
                '<input id="value" name="value" type="number" placeholder="Value" min="101" step="any" />',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function realUpperValidationBoundProvider(): array
    {
        return [
            'inclusive' => [
                'lessThanOrEquals',
                '<input id="value" name="value" type="number" placeholder="Value" max="1000" step="any" />',
            ],
            'exclusive' => [
                'lessThan',
                '<input id="value" name="value" type="number" placeholder="Value" max="999" step="any" />',
            ],
        ];
    }

    public function testRealBetweenValidation(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INTEGER NOT NULL,
                value REAL NULL DEFAULT NULL,
                PRIMARY KEY (id)
            )
        SQL);

        $this->validator->add('value', Rule::between(100, 1000));

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="100" max="1000" step="any" />',
            $this->view->Form->input('value')
        );
    }

    public function testRealEntityValue(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INTEGER NOT NULL,
                value REAL NULL DEFAULT NULL,
                PRIMARY KEY (id)
            )
        SQL);

        $entity = $this->model->newEntity([
            'value' => 100.123,
        ]);

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="100.123" placeholder="Value" step="any" />',
            $this->view->Form->input('value')
        );
    }

    #[DataProvider('realLowerValidationBoundProvider')]
    public function testRealLowerValidationBound(string $rule, string $expected): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INTEGER NOT NULL,
                value REAL NULL DEFAULT NULL,
                PRIMARY KEY (id)
            )
        SQL);

        $this->validator->add('value', Rule::$rule(100));

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            $expected,
            $this->view->Form->input('value')
        );
    }

    public function testRealMinMaxSchema(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INTEGER NOT NULL,
                value REAL NULL DEFAULT NULL,
                PRIMARY KEY (id)
            )
        SQL);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" step="any" />',
            $this->view->Form->input('value')
        );
    }

    public function testRealRequiredValidation(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INTEGER NOT NULL,
                value REAL NULL DEFAULT NULL,
                PRIMARY KEY (id)
            )
        SQL);

        $this->validator->add('value', Rule::required());

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" step="any" required />',
            $this->view->Form->input('value')
        );
    }

    public function testRealSchemaDefaultValue(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INTEGER NOT NULL,
                value REAL NOT NULL DEFAULT 100.123,
                PRIMARY KEY (id)
            )
        SQL);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="100.123" placeholder="Value" step="any" />',
            $this->view->Form->input('value')
        );
    }

    public function testRealUnsignedMinMaxSchema(): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INTEGER NOT NULL,
                value UNSIGNED REAL NULL DEFAULT NULL,
                PRIMARY KEY (id)
            )
        SQL);

        $entity = $this->model->newEmptyEntity();

        $this->view->Form->open($entity);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="0" step="any" />',
            $this->view->Form->input('value')
        );
    }

    #[DataProvider('realUpperValidationBoundProvider')]
    public function testRealUpperValidationBound(string $rule, string $expected): void
    {
        $this->db->query(<<<'SQL'
            CREATE TABLE contexts (
                id INTEGER NOT NULL,
                value REAL NULL DEFAULT NULL,
                PRIMARY KEY (id)
            )
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

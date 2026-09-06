<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Form;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait IntegerTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function integerLowerValidationBoundProvider(): array
    {
        return [
            'inclusive' => [
                'greaterThanOrEquals',
                '<input id="value" name="value" type="number" placeholder="Value" min="100" step="1" />',
            ],
            'exclusive' => [
                'greaterThan',
                '<input id="value" name="value" type="number" placeholder="Value" min="101" step="1" />',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function integerUpperValidationBoundProvider(): array
    {
        return [
            'inclusive' => [
                'lessThanOrEquals',
                '<input id="value" name="value" type="number" placeholder="Value" max="1000" step="1" />',
            ],
            'exclusive' => ['lessThan', '<input id="value" name="value" type="number" placeholder="Value" max="999" step="1" />'],
        ];
    }

    public function testIntegerBetweenValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'integer',
        ]);

        $this->validator->add('value', Rule::between(100, 1000));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="100" max="1000" step="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testIntegerFormValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'integer',
        ]);

        $this->form->set('value', 999);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="999" placeholder="Value" step="1" />',
            $this->view->Form->input('value')
        );
    }

    #[DataProvider('integerLowerValidationBoundProvider')]
    public function testIntegerLowerValidationBound(string $rule, string $expected): void
    {
        $this->schema->addField('value', [
            'type' => 'integer',
        ]);

        $this->validator->add('value', Rule::$rule(100));

        $this->view->Form->open($this->form);

        $this->assertSame(
            $expected,
            $this->view->Form->input('value')
        );
    }

    public function testIntegerMinMaxSchema(): void
    {
        $this->schema->addField('value', [
            'type' => 'integer',
            'precision' => 10,
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="-9999999999" max="9999999999" step="1" />',
            $this->view->Form->input('value')
        );
    }

    public function testIntegerRequiredValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'integer',
        ]);

        $this->validator->add('value', Rule::required());

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" step="1" required />',
            $this->view->Form->input('value')
        );
    }

    public function testIntegerSchemaDefaultValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'integer',
            'default' => 999,
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="999" placeholder="Value" step="1" />',
            $this->view->Form->input('value')
        );
    }

    #[DataProvider('integerUpperValidationBoundProvider')]
    public function testIntegerUpperValidationBound(string $rule, string $expected): void
    {
        $this->schema->addField('value', [
            'type' => 'integer',
        ]);

        $this->validator->add('value', Rule::$rule(1000));

        $this->view->Form->open($this->form);

        $this->assertSame(
            $expected,
            $this->view->Form->input('value')
        );
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Form;

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
     * @return array<string, array{array{type: string, precision: int, scale: int}, string}>
     */
    public static function decimalPrecisionScaleProvider(): array
    {
        return [
            'precision equals scale' => [
                [
                    'type' => 'decimal',
                    'precision' => 2,
                    'scale' => 2,
                ],
                '<input id="value" name="value" type="number" placeholder="Value" min="-0.99" max="0.99" step="0.01" />',
            ],
            'precision overflow' => [
                [
                    'type' => 'decimal',
                    'precision' => 30,
                    'scale' => 0,
                ],
                '<input id="value" name="value" type="number" placeholder="Value" step="1" />',
            ],
            'zero scale' => [
                [
                    'type' => 'decimal',
                    'precision' => 10,
                    'scale' => 0,
                ],
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
        $this->schema->addField('value', [
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
        ]);

        $this->validator->add('value', Rule::between(100, 1000));

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="100" max="1000" step="0.01" />',
            $this->view->Form->input('value')
        );
    }

    public function testDecimalFormValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
        ]);

        $this->form->set('value', 100.99);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="100.99" placeholder="Value" min="-99999999.99" max="99999999.99" step="0.01" />',
            $this->view->Form->input('value')
        );
    }

    #[DataProvider('decimalLowerValidationBoundProvider')]
    public function testDecimalLowerValidationBound(string $rule, string $expected): void
    {
        $this->schema->addField('value', [
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
        ]);

        $this->validator->add('value', Rule::$rule(100));

        $this->view->Form->open($this->form);

        $this->assertSame(
            $expected,
            $this->view->Form->input('value')
        );
    }

    public function testDecimalMinMaxSchema(): void
    {
        $this->schema->addField('value', [
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="-99999999.99" max="99999999.99" step="0.01" />',
            $this->view->Form->input('value')
        );
    }

    /**
     * @param array{type: string, precision: int, scale: int} $options
     */
    #[DataProvider('decimalPrecisionScaleProvider')]
    public function testDecimalPrecisionScale(array $options, string $expected): void
    {
        $this->schema->addField('value', $options);

        $this->view->Form->open($this->form);

        $this->assertSame(
            $expected,
            $this->view->Form->input('value')
        );
    }

    public function testDecimalRequiredValidation(): void
    {
        $this->schema->addField('value', [
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
        ]);

        $this->validator->add('value', Rule::required());

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" placeholder="Value" min="-99999999.99" max="99999999.99" step="0.01" required />',
            $this->view->Form->input('value')
        );
    }

    public function testDecimalSchemaDefaultValue(): void
    {
        $this->schema->addField('value', [
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
            'default' => 100.99,
        ]);

        $this->view->Form->open($this->form);

        $this->assertSame(
            '<input id="value" name="value" type="number" value="100.99" placeholder="Value" min="-99999999.99" max="99999999.99" step="0.01" />',
            $this->view->Form->input('value')
        );
    }

    #[DataProvider('decimalUpperValidationBoundProvider')]
    public function testDecimalUpperValidationBound(string $rule, string $expected): void
    {
        $this->schema->addField('value', [
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
        ]);

        $this->validator->add('value', Rule::$rule(1000));

        $this->view->Form->open($this->form);

        $this->assertSame(
            $expected,
            $this->view->Form->input('value')
        );
    }
}

<?php
declare(strict_types=1);

namespace Fyre\Form;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Type;

/**
 * Represents a form.
 *
 * Note: Forms are executed by parsing input values using the configured {@see Schema} field
 * types, optionally validating the parsed data using the configured {@see Validator}, and
 * then processing the data via {@see process()}.
 */
class Form
{
    use DebugTrait;

    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @var array<string, string[]>
     */
    protected array $errors = [];

    protected Schema $schema;

    protected Validator $validator;

    /**
     * Constructs a Form.
     *
     * @param Container $container The Container.
     */
    public function __construct(
        protected Container $container,
    ) {}

    /**
     * Builds the form schema.
     *
     * @param Schema $schema The Schema.
     * @return Schema The Schema instance.
     */
    public function buildSchema(Schema $schema): Schema
    {
        return $schema;
    }

    /**
     * Builds the form Validator.
     *
     * @param Validator $validator The Validator.
     * @return Validator The Validator instance.
     */
    public function buildValidation(Validator $validator): Validator
    {
        return $validator;
    }

    /**
     * Executes the form validation and processing.
     *
     * Note: Data keys that are not present in the {@see Schema} are preserved unchanged.
     * Schema fields are parsed via {@see Field::type()} {@see Type::parse()}.
     *
     * @param array<string, mixed> $data The form data.
     * @param bool $validate Whether to validate the form data.
     * @return bool Whether the form was successfully processed.
     */
    public function execute(array $data, bool $validate = true): bool
    {
        $schema = $this->getSchema();

        $this->data = [];
        foreach ($data as $key => $value) {
            if (!$schema->hasField($key)) {
                $this->data[$key] = $value;

                continue;
            }

            $this->data[$key] = $schema->field($key)->type()->parse($value);
        }

        if ($validate && !$this->validate($this->data)) {
            return false;
        }

        return $this->process($this->data);
    }

    /**
     * Returns the form data for a single field.
     *
     * @param string $field The field name.
     * @return mixed The single field value.
     */
    public function get(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    /**
     * Returns the form data.
     *
     * @return array<string, mixed> The form data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns the validation errors for a field.
     *
     * @param string $field The field name.
     * @return string[] The validation errors for the field.
     */
    public function getError(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Returns the form validation errors.
     *
     * @return array<string, string[]> The form validation errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Returns the form schema.
     *
     * @return Schema The form Schema.
     */
    public function getSchema(): Schema
    {
        return $this->schema ??= $this->buildSchema($this->container->build(Schema::class));
    }

    /**
     * Returns the form Validator.
     *
     * @return Validator The form Validator.
     */
    public function getValidator(): Validator
    {
        return $this->validator ??= $this->buildValidation($this->container->build(Validator::class));
    }

    /**
     * Sets a form field value.
     *
     * @param string $field The field name.
     * @param mixed $value The field value.
     * @return static The Form instance.
     */
    public function set(string $field, mixed $value): static
    {
        $this->data[$field] = $value;

        return $this;
    }

    /**
     * Sets the form data.
     *
     * @param array<string, mixed> $data The form data.
     * @return static The Form instance.
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Sets the form schema.
     *
     * @param Schema $schema The form Schema.
     * @return static The Form instance.
     */
    public function setSchema(Schema $schema): static
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * Sets the form Validator.
     *
     * @param Validator $validator The form Validator.
     * @return static The Form instance.
     */
    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Validates the form data.
     *
     * @param array<string, mixed> $data The form data.
     * @return bool Whether the form data is valid.
     */
    public function validate(array $data): bool
    {
        $this->errors = $this->getValidator()->validate($data);

        return $this->errors === [];
    }

    /**
     * Processes the form data.
     *
     * @param array<string, mixed> $data The form data.
     * @return bool Whether the form data was successfully processed.
     */
    protected function process(array $data): bool
    {
        return true;
    }
}

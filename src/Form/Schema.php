<?php
declare(strict_types=1);

namespace Fyre\Form;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use InvalidArgumentException;

use function array_keys;
use function sprintf;

/**
 * Represents a form schema.
 */
class Schema
{
    use DebugTrait;

    /**
     * @var array<string, Field>
     */
    protected array $fields = [];

    /**
     * Constructs a Schema.
     *
     * @param Container $container The Container.
     */
    public function __construct(
        protected Container $container,
    ) {}

    /**
     * Adds a field to the form.
     *
     * @param string $name The field name.
     * @param array<string, mixed> $options Additional constructor arguments for the field.
     * @return static The Schema instance.
     */
    public function addField(string $name, array $options = []): static
    {
        $this->fields[$name] = $this->container->build(Field::class, [
            'name' => $name,
            ...$options,
        ]);

        return $this;
    }

    /**
     * Returns a field from the form.
     *
     * @param string $name The field name.
     * @return Field The form field.
     *
     * @throws InvalidArgumentException If the field does not exist.
     */
    public function field(string $name): Field
    {
        if (!isset($this->fields[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Schema field `%s` does not exist.',
                $name
            ));
        }

        return $this->fields[$name];
    }

    /**
     * Returns the form field names.
     *
     * @return string[] The form field names.
     */
    public function fieldNames(): array
    {
        return array_keys($this->fields);
    }

    /**
     * Returns the form fields.
     *
     * @return array<string, Field> The form fields.
     */
    public function fields(): array
    {
        return $this->fields;
    }

    /**
     * Checks whether the form has a field.
     *
     * @param string $name The field name.
     * @return bool Whether the form has the field.
     */
    public function hasField(string $name): bool
    {
        return isset($this->fields[$name]);
    }

    /**
     * Removes a field from the form.
     *
     * @param string $name The field name.
     * @return static The Schema instance.
     */
    public function removeField(string $name): static
    {
        unset($this->fields[$name]);

        return $this;
    }
}

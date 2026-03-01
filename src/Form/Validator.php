<?php
declare(strict_types=1);

namespace Fyre\Form;

use Closure;
use Fyre\Core\Container;
use Fyre\Core\Lang;
use Fyre\Core\Traits\DebugTrait;

use function array_key_exists;
use function array_unique;
use function is_string;

/**
 * Validates data against rules.
 *
 * Note: Validation errors are returned as unique message lists per field. Rule callbacks may
 * return `true` for pass, `false` for failure (using the rule message), or a string to
 * provide a custom failure message.
 *
 * Note: A field is considered "set" using {@see array_key_exists()}, so null values are
 * treated as present. Empty values are `null`, empty string, or empty array.
 */
class Validator
{
    use DebugTrait;

    /**
     * @var array<string, Rule[]>
     */
    protected array $fields = [];

    /**
     * Constructs a Validator.
     *
     * @param Container $container The Container.
     * @param Lang $lang The Lang.
     */
    public function __construct(
        protected Container $container,
        protected Lang $lang
    ) {}

    /**
     * Adds a validation rule.
     *
     * @param string $field The field name.
     * @param Closure|Rule $rule The Rule.
     * @param string|null $on The rule type.
     * @param string|null $message The rule message.
     * @param string|null $name The rule name.
     * @return static The Validator.
     */
    public function add(
        string $field,
        Closure|Rule $rule,
        string|null $on = null,
        string|null $message = null,
        string|null $name = null,
    ): static {
        if ($rule instanceof Closure) {
            $rule = new Rule($rule);
        }

        if ($name) {
            $rule->setName($name);
        }

        if ($on) {
            $rule->setType($on);
        }

        if ($message) {
            $rule->setMessage($message);
        }

        $this->fields[$field] ??= [];
        $this->fields[$field][] = $rule;

        return $this;
    }

    /**
     * Clears all rules from the Validator.
     */
    public function clear(): void
    {
        $this->fields = [];
    }

    /**
     * Returns the rules for a field.
     *
     * @param string $field The field name.
     * @return Rule[] The rules.
     */
    public function getFieldRules(string $field): array
    {
        return $this->fields[$field] ?? [];
    }

    /**
     * Removes a validation rule.
     *
     * @param string $field The field name.
     * @param string|null $name The rule name.
     * @return bool Whether the rule was removed.
     */
    public function remove(string $field, string|null $name = null): bool
    {
        if (!isset($this->fields[$field])) {
            return false;
        }

        if ($name === null) {
            unset($this->fields[$field]);

            return true;
        }

        $hasRule = false;
        $newRules = [];

        foreach ($this->fields[$field] as $rule) {
            if ($rule->getName() === $name) {
                $hasRule |= true;

                continue;
            }

            $newRules[] = $rule;
        }

        if (!$hasRule) {
            return false;
        }

        if ($newRules === []) {
            unset($this->fields[$field]);
        } else {
            $this->fields[$field] = $newRules;
        }

        return true;
    }

    /**
     * Performs validation and returns any errors.
     *
     * Note: A field is considered \"set\" using {@see array_key_exists()}, so null values
     * are treated as present. Empty values are `null`, empty string, or empty array.
     *
     * @param array<string, mixed> $data The data to validate.
     * @param string|null $type The type of validation to perform.
     * @return array<string, string[]> The validation errors.
     */
    public function validate(array $data, string|null $type = null): array
    {
        $errors = [];

        foreach ($this->fields as $field => $rules) {
            $value = $data[$field] ?? null;

            $hasField = array_key_exists($field, $data);
            $hasValue = $value !== null && $value !== '' && $value !== [];

            $fieldErrors = [];
            foreach ($rules as $rule) {
                if (!$rule->checkType($type)) {
                    continue;
                }

                if (!$hasField && $rule->skipNotSet()) {
                    continue;
                }

                if (!$hasValue && $rule->skipEmpty()) {
                    continue;
                }

                $result = $this->container->call($rule->getCallback(), [
                    'value' => $value,
                    'data' => $data,
                    'field' => $field,
                ]);

                if ($result === true) {
                    continue;
                }

                if ($result === null || $result === false || $result === '') {
                    $result = $rule->getMessage();
                }

                if ($result === null || $result === '') {
                    $name = $rule->getName();

                    if ($name) {
                        $arguments = $rule->getArguments();
                        $arguments['field'] = $field;

                        $result = $this->lang->get('Validation.'.$name, $arguments);
                    }
                }

                if (!is_string($result)) {
                    $result = 'invalid';
                }

                $fieldErrors[] = $result;
            }

            if ($fieldErrors !== []) {
                $errors[$field] = array_unique($fieldErrors);
            }
        }

        return $errors;
    }
}

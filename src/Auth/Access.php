<?php
declare(strict_types=1);

namespace Fyre\Auth;

use Closure;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Http\Exceptions\ForbiddenException;
use Fyre\ORM\Entity;
use Fyre\ORM\Model;
use Fyre\ORM\ModelRegistry;
use Fyre\Utility\Inflector;
use ReflectionClass;
use ReflectionFunction;

use function array_shift;
use function array_values;
use function count;
use function is_string;
use function method_exists;

/**
 * Evaluates authorization rules and policies for the current user.
 */
class Access
{
    use DebugTrait;
    use MacroTrait;

    /**
     * @var Closure[]
     */
    protected array $afterRules = [];

    /**
     * @var Closure[]
     */
    protected array $beforeRules = [];

    /**
     * @var array<string, Closure>
     */
    protected array $rules = [];

    /**
     * Constructs an Access.
     *
     * @param Closure $userResolver The Closure used to resolve the user.
     * @param Inflector $inflector The Inflector.
     * @param PolicyRegistry $policyRegistry The PolicyRegistry.
     * @param ModelRegistry $modelRegistry The ModelRegistry.
     */
    public function __construct(
        protected Closure $userResolver,
        protected Inflector $inflector,
        protected PolicyRegistry $policyRegistry,
        protected ModelRegistry $modelRegistry
    ) {}

    /**
     * Registers an after-rule callback.
     *
     * The callback receives the resolved user (or null), the rule name, the current result (or null),
     * and any additional arguments. Returning `true` or `false` short-circuits evaluation; returning
     * `null` defers to the next callback.
     *
     * @param Closure $afterRule The callback Closure.
     */
    public function after(Closure $afterRule): void
    {
        $this->afterRules[] = $afterRule;
    }

    /**
     * Checks whether an access rule is allowed.
     *
     * Rules are evaluated in order: `before` callbacks, named rules, policies, then `after`
     * callbacks. The first non-null result is treated as authoritative. If no rule applies,
     * access is denied.
     *
     * @param string $rule The access rule name.
     * @param mixed ...$args Additional arguments for the access rule.
     * @return bool Whether the access rule is allowed.
     */
    public function allows(string $rule, mixed ...$args): bool
    {
        $user = ($this->userResolver)();

        $result = null;

        foreach ($this->beforeRules as $beforeRule) {
            if (!$user && static::isClosureUserRequired($beforeRule)) {
                continue;
            }

            $result ??= $beforeRule($user, $rule, ...$args);
        }

        $result ??= $this->checkRule($rule, $args);
        $result ??= $this->checkPolicy($rule, $args);

        foreach ($this->afterRules as $afterRule) {
            if (!$user && static::isClosureUserRequired($afterRule)) {
                continue;
            }

            $afterResult = $afterRule($user, $rule, $result, ...$args);
            $result ??= $afterResult;
        }

        return (bool) $result;
    }

    /**
     * Checks whether any access rule is allowed.
     *
     * @param string[] $rules The access rule names.
     * @param mixed ...$args Additional arguments for the access rule.
     * @return bool Whether any access rule is allowed.
     */
    public function any(array $rules, mixed ...$args): bool
    {
        foreach ($rules as $rule) {
            if ($this->allows($rule, ...$args)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Authorizes an access rule.
     *
     * @param string $rule The access rule name.
     * @param mixed ...$args Additional arguments for the access rule.
     *
     * @throws ForbiddenException If access is not authorized.
     */
    public function authorize(string $rule, mixed ...$args): void
    {
        if (!$this->allows($rule, ...$args)) {
            throw new ForbiddenException();
        }
    }

    /**
     * Registers a before-rule callback.
     *
     * The callback receives the resolved user (or null), the rule name, and any additional arguments.
     * Returning `true` or `false` short-circuits evaluation; returning `null` defers to the next callback.
     *
     * @param Closure $beforeRule The callback Closure.
     */
    public function before(Closure $beforeRule): void
    {
        $this->beforeRules[] = $beforeRule;
    }

    /**
     * Clears all rules and callbacks.
     */
    public function clear(): void
    {
        $this->afterRules = [];
        $this->beforeRules = [];
        $this->rules = [];
    }

    /**
     * Defines an access rule.
     *
     * @param string $rule The access rule name.
     * @param Closure $callback The callback Closure that evaluates the access rule.
     */
    public function define(string $rule, Closure $callback): void
    {
        $this->rules[$rule] = $callback;
    }

    /**
     * Checks whether an access rule is not allowed.
     *
     * @param string $rule The access rule name.
     * @param mixed ...$args Additional arguments for the access rule.
     * @return bool Whether the access rule is not allowed.
     */
    public function denies(string $rule, mixed ...$args): bool
    {
        return !$this->allows($rule, ...$args);
    }

    /**
     * Checks whether no access rule is allowed.
     *
     * @param string[] $rules The access rule names.
     * @param mixed ...$args Additional arguments for the access rule.
     * @return bool Whether no access rule is allowed.
     */
    public function none(array $rules, mixed ...$args): bool
    {
        return !$this->any($rules, ...$args);
    }

    /**
     * Checks a Policy rule.
     *
     * Note: The policy alias is derived from the first argument when it is a string, a
     * {@see Entity}, or a {@see Model}. If additional arguments are provided and no entity
     * instance is available, the entity is loaded using the model primary key values.
     *
     * @param string $rule The Policy rule name.
     * @param array<mixed> $args Additional arguments for the Policy rule.
     * @return bool|null The Policy rule result or null if no policy applies.
     */
    protected function checkPolicy(string $rule, array $args): bool|null
    {
        $value = array_shift($args);
        $alias = null;
        $item = null;

        if (is_string($value)) {
            $alias = $value;
        } else if ($value instanceof Entity) {
            $item = $value;
            $alias = $item->getSource();
        } else if ($value instanceof Model) {
            $alias = $value->getAlias();
        }

        if (!$alias) {
            return null;
        }

        $policy = $this->policyRegistry->use($alias);
        $method = $this->inflector->variable($rule);

        if (!$policy || !method_exists($policy, $method)) {
            return null;
        }

        if ($args !== [] && $item === null) {
            $args = array_values($args);
            $alias = $this->policyRegistry->resolveAlias($alias);
            $item = $this->modelRegistry->use($alias)->get($args);
        }

        $user = ($this->userResolver)();

        if (!$user || !$item) {
            $params = new ReflectionClass($policy)
                ->getMethod($method)
                ->getParameters();

            if (!$user && $params !== [] && !$params[0]->allowsNull()) {
                return false;
            }

            if (!$item && count($params) > 1 && !$params[1]->allowsNull()) {
                return false;
            }
        }

        return $policy->$method($user, $item);
    }

    /**
     * Checks an access rule.
     *
     * @param string $rule The access rule name.
     * @param array<mixed> $args Additional arguments for the access rule.
     * @return bool|null The access rule result or null if no rule applies.
     */
    protected function checkRule(string $rule, array $args): bool|null
    {
        if (!isset($this->rules[$rule])) {
            return null;
        }

        $user = ($this->userResolver)();

        if (!$user && static::isClosureUserRequired($this->rules[$rule])) {
            return false;
        }

        return $this->rules[$rule]($user, ...$args);
    }

    /**
     * Checks whether the user parameter is required for a Closure.
     *
     * @param Closure $callback The Closure.
     * @return bool Whether the user parameter is required for a Closure.
     */
    protected static function isClosureUserRequired(Closure $callback): bool
    {
        $params = new ReflectionFunction($callback)->getParameters();

        return $params !== [] && !$params[0]->allowsNull();
    }
}

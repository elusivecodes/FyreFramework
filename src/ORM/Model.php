<?php
declare(strict_types=1);

namespace Fyre\ORM;

use ArrayObject;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\QueryGenerator;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\Schema\Table;
use Fyre\Event\EventListenerInterface;
use Fyre\Event\EventManager;
use Fyre\Event\Traits\EventDispatcherTrait;
use Fyre\Form\Validator;
use Fyre\ORM\Attributes\ModelAttribute;
use Fyre\ORM\Exceptions\OrmException;
use Fyre\ORM\Queries\DeleteQuery;
use Fyre\ORM\Queries\InsertQuery;
use Fyre\ORM\Queries\SelectQuery;
use Fyre\ORM\Queries\UpdateBatchQuery;
use Fyre\ORM\Queries\UpdateQuery;
use Fyre\ORM\Queries\UpsertQuery;
use Fyre\ORM\Relationships\BelongsTo;
use Fyre\ORM\Relationships\HasMany;
use Fyre\ORM\Relationships\HasOne;
use Fyre\ORM\Relationships\ManyToMany;
use Fyre\Utility\EnumHelper;
use Fyre\Utility\Inflector;
use InvalidArgumentException;
use ReflectionAttribute;
use ReflectionClass;
use Traversable;

use function array_diff_assoc;
use function array_filter;
use function array_find;
use function array_first;
use function array_flip;
use function array_intersect;
use function array_intersect_key;
use function array_key_exists;
use function array_map;
use function array_merge;
use function array_reduce;
use function array_reverse;
use function array_values;
use function assert;
use function count;
use function ctype_upper;
use function explode;
use function gettype;
use function is_a;
use function is_array;
use function is_numeric;
use function is_object;
use function is_string;
use function iterator_to_array;
use function preg_replace;
use function sprintf;

use const ARRAY_FILTER_USE_KEY;

/**
 * Represents an ORM model and provides persistence/query helpers.
 */
class Model implements EventListenerInterface
{
    use DebugTrait;
    use EventDispatcherTrait;
    use MacroTrait;

    public const READ = 'read';

    public const WRITE = 'write';

    protected string $alias;

    protected string|null $autoIncrementKey = null;

    protected string $classAlias;

    /**
     * @var array<string, string>
     */
    protected array $connectionKeys = [
        self::WRITE => 'default',
    ];

    /**
     * @var array<string, Connection>
     */
    protected array $connections = [];

    protected string|null $displayName = null;

    /**
     * @var string[]
     */
    protected array $primaryKey;

    /**
     * @var array<string, Relationship>
     */
    protected array $relationships = [];

    protected string|null $routeKey = null;

    protected RuleSet $rules;

    protected string $table;

    protected Validator $validator;

    /**
     * Merges contain data recursively.
     *
     * @param array<string, array<string, mixed>> $contain The original contain.
     * @param array<string, array<string, mixed>> $newContain The new contain.
     * @param string $containKey The key for the contains.
     * @return array<string, array<string, mixed>> The merged contain data.
     */
    public static function mergeContain(array $contain, array $newContain, string $containKey = 'contain'): array
    {
        foreach ($newContain as $name => $data) {
            if (!array_key_exists($name, $contain)) {
                $contain[$name] = $data;

                continue;
            }

            foreach ($data as $key => $value) {
                if ($key === $containKey) {
                    $contain[$name][$key] = static::mergeContain($contain[$name][$key], $value, $containKey);
                } else if ($key === 'callback') {
                    $oldValue = $contain[$name][$key] ?? null;
                    if ($oldValue === null) {
                        $contain[$name][$key] = $value;
                    } else if ($value !== null) {
                        $contain[$name][$key] = static fn(SelectQuery $query): SelectQuery => $value($oldValue($query));
                    }
                } else {
                    $contain[$name][$key] = $value;
                }
            }
        }

        return $contain;
    }

    /**
     * Normalizes contain data.
     *
     * @param array<mixed>|string $contain The contain data.
     * @param Model $model The Model.
     * @param string $containKey The key for the contains.
     * @param int $depth The contain depth.
     * @return array<string, mixed> The normalized contain data.
     *
     * @throws OrmException If a relationship is not valid.
     */
    public static function normalizeContain(array|string $contain, Model $model, string $containKey = 'contain', int $depth = 0): array
    {
        $normalized = [
            $containKey => [],
        ];

        if ($contain === '' || $contain === []) {
            return $normalized;
        }

        if (is_string($contain)) {
            $contain = array_reduce(
                explode('.', $contain) |> array_reverse(...),
                static fn(array $acc, string $value): array => $value ?
                    [
                        $value => [
                            $containKey => $acc,
                        ],
                    ] :
                    $acc,
                []
            );
        }

        foreach ($contain as $key => $value) {
            if (is_numeric($key)) {
                $newContain = static::normalizeContain($value, $model, $containKey, $depth);
                $normalized[$containKey] = static::mergeContain($normalized[$containKey], $newContain[$containKey], $containKey);

                continue;
            }

            $relationship = $model->getRelationship($key);

            if (!$relationship) {
                throw new OrmException(sprintf(
                    'Model `%s` does not have a relationship to `%s`.',
                    $model->getAlias(),
                    $key
                ));
            }

            if (!is_array($value)) {
                $value = [$containKey => $value];
            }

            $value[$containKey] ??= [];

            foreach ($value as $k => $v) {
                if (is_numeric($k)) {
                    $value[$containKey][] = $v;
                    unset($value[$k]);
                } else if (ctype_upper($k[0])) {
                    $value[$containKey][$k] = $v;
                    unset($value[$k]);
                }
            }

            $value[$containKey] = static::normalizeContain($value[$containKey], $relationship->getTarget(), $containKey, $depth + 1)[$containKey];
            $normalized[$containKey] = static::mergeContain($normalized[$containKey], [$key => $value], $containKey);
        }

        return $normalized;
    }

    /**
     * Constructs a Model.
     *
     * @param Container $container The Container.
     * @param ConnectionManager $connectionManager The ConnectionManager.
     * @param SchemaRegistry $schemaRegistry The SchemaRegistry.
     * @param EntityLocator $entityLocator The EntityLocator.
     * @param Inflector $inflector The Inflector.
     * @param EventManager $eventManager The EventManager.
     */
    public function __construct(
        protected Container $container,
        protected ConnectionManager $connectionManager,
        protected SchemaRegistry $schemaRegistry,
        protected EntityLocator $entityLocator,
        protected Inflector $inflector,
        EventManager $eventManager
    ) {
        $this->eventManager = $container->build(EventManager::class, [
            'parentEventManager' => $eventManager,
        ]);

        $this->eventManager->addListener($this);

        $reflection = new ReflectionClass(static::class);
        $attributes = $reflection->getAttributes(ModelAttribute::class, ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            $instance->loadModel($this);
        }

        $this->initialize();
    }

    /**
     * Returns a Relationship.
     *
     * @param string $name The name.
     * @return Relationship The Relationship instance.
     *
     * @throws InvalidArgumentException If the relationship does not exist.
     */
    public function __get(string $name): Relationship
    {
        if (isset($this->relationships[$name])) {
            return $this->relationships[$name];
        }

        throw new InvalidArgumentException(sprintf(
            'Model `%s` does not have a relationship to `%s`.',
            $this->getAlias(),
            $name
        ));
    }

    /**
     * Checks whether a Relationship exists.
     *
     * @param string $name The relationship name.
     * @return bool Whether the Relationship exists.
     */
    public function __isset(string $name): bool
    {
        return $this->hasRelationship($name);
    }

    /**
     * Adds a Relationship.
     *
     * @param Relationship $relationship The Relationship.
     * @return static The Model instance.
     *
     * @throws OrmException If the relationship alias or property is already used.
     */
    public function addRelationship(Relationship $relationship): static
    {
        $name = $relationship->getName();

        if (isset($this->relationships[$name])) {
            throw new OrmException(sprintf(
                'Model `%s` already has a relationship to `%s`.',
                $this->getAlias(),
                $name
            ));
        }

        $property = $relationship->getProperty();

        if ($this->getSchema()->hasColumn($property)) {
            throw new OrmException(sprintf(
                'Model `%s` relationship `%s` property conflicts with table column `%s`.',
                $this->getAlias(),
                $name,
                $property
            ));
        }

        $this->relationships[$name] = $relationship;

        return $this;
    }

    /**
     * Aliases a field name.
     *
     * @param string $field The field name.
     * @param string|null $alias The alias.
     * @return string The aliased field.
     */
    public function aliasField(string $field, string|null $alias = null): string
    {
        if (!$this->getSchema()->hasColumn($field)) {
            return $field;
        }

        $alias ??= $this->getAlias();

        return $alias.'.'.$field;
    }

    /**
     * Creates a "belongs to" relationship.
     *
     * @param string $name The relationship name.
     * @param array<string, mixed> $options The relationship options.
     * @return BelongsTo The new BelongsTo instance.
     */
    public function belongsTo(string $name, array $options = []): BelongsTo
    {
        $options['source'] = $this;

        $relationship = $this->container->build(BelongsTo::class, ['name' => $name, 'options' => $options]);

        $this->addRelationship($relationship);

        return $relationship;
    }

    /**
     * Builds the model RuleSet.
     *
     * @param RuleSet $rules The RuleSet.
     * @return RuleSet The RuleSet instance.
     */
    public function buildRules(RuleSet $rules): RuleSet
    {
        return $rules;
    }

    /**
     * Builds the model Validator.
     *
     * @param Validator $validator The Validator.
     * @return Validator The Validator instance.
     */
    public function buildValidation(Validator $validator): Validator
    {
        return $validator;
    }

    /**
     * Deletes an Entity.
     *
     * @param Entity $entity The Entity.
     * @param bool $cascade Whether to delete related children.
     * @param bool $events Whether to trigger events.
     * @param mixed ...$options The delete options.
     * @return bool Whether the delete was successful.
     */
    public function delete(
        Entity $entity,
        bool $cascade = true,
        bool $events = true,
        mixed ...$options
    ): bool {
        $options['cascade'] = $cascade;
        $options['events'] = $events;

        $connection = $this->getConnection();

        $connection->begin();

        if (!$this->performDelete($entity, $options)) {
            $connection->rollback();

            static::resetParents([$entity], $this);
            static::resetChildren([$entity], $this);

            $entity->clearTemporaryFields();

            return false;
        }

        if ($events) {
            $connection->afterCommit(function() use ($entity, $options): void {
                $this->dispatchEvent('ORM.afterDeleteCommit', ['entity' => $entity, 'options' => $options]);
            }, 100);
        }

        $connection->afterCommit(function() use ($entity): void {
            static::cleanEntities([$entity], $this);
        }, 200);

        $connection->commit();

        return true;
    }

    /**
     * Deletes all rows matching conditions.
     *
     * @param array<mixed> $conditions The conditions.
     * @return int The number of rows affected.
     */
    public function deleteAll(array $conditions): int
    {
        return $this->deleteQuery()
            ->where($conditions)
            ->execute()
            ->count();
    }

    /**
     * Deletes multiple entities.
     *
     * @param iterable<Entity> $entities The entities.
     * @param bool $cascade Whether to delete related children.
     * @param bool $events Whether to trigger events.
     * @param mixed ...$options The delete options.
     * @return bool Whether the delete was successful.
     */
    public function deleteMany(
        array|Traversable $entities,
        bool $cascade = true,
        bool $events = true,
        mixed ...$options
    ): bool {
        if (!is_array($entities)) {
            $entities = iterator_to_array($entities);
        }

        if ($entities === []) {
            return true;
        }

        static::checkEntities($entities);

        $options['cascade'] = $cascade;
        $options['events'] = $events;

        if (count($entities) === 1) {
            return $this->delete($entities[0], ...$options);
        }

        $connection = $this->getConnection();

        $connection->begin();

        $result = true;
        foreach ($entities as $entity) {
            if (!$this->performDelete($entity, $options)) {
                $result = false;
                break;
            }
        }

        if (!$result) {
            $connection->rollback();

            static::resetParents($entities, $this);
            static::resetChildren($entities, $this);

            foreach ($entities as $entity) {
                $entity->clearTemporaryFields();
            }

            return false;
        }

        if ($events) {
            $connection->afterCommit(function() use ($entities, $options): void {
                foreach ($entities as $entity) {
                    $this->dispatchEvent('ORM.afterDeleteCommit', ['entity' => $entity, 'options' => $options]);
                }
            }, 100);
        }

        $connection->afterCommit(function() use ($entities): void {
            static::cleanEntities($entities, $this);
        }, 200);

        $connection->commit();

        return true;
    }

    /**
     * Creates a new DeleteQuery.
     *
     * @param array<string, mixed> $options The options for the query.
     * @return DeleteQuery The new DeleteQuery instance.
     */
    public function deleteQuery(array $options = []): DeleteQuery
    {
        return new DeleteQuery($this, $options);
    }

    /**
     * Checks whether matching rows exist.
     *
     * @param array<mixed> $conditions The conditions.
     * @return bool Whether matching rows exist.
     */
    public function exists(array $conditions): bool
    {
        return $this->find()
            ->disableAutoFields()
            ->where($conditions)
            ->limit(1)
            ->count() > 0;
    }

    /**
     * Creates a new SelectQuery.
     *
     * @param array<mixed>|string|null $fields The SELECT fields.
     * @param array<mixed>|string|null $contain The contain relationships.
     * @param array<array<string, mixed>>|null $join The JOIN tables.
     * @param array<mixed>|string|null $conditions The WHERE conditions.
     * @param array<string>|string|null $orderBy The ORDER BY fields.
     * @param string|string[]|null $groupBy The GROUP BY fields.
     * @param array<mixed>|string|null $having The HAVING conditions.
     * @param int|null $limit The LIMIT clause.
     * @param int|null $offset The OFFSET clause.
     * @param string|null $epilog The epilog.
     * @param string $connectionType The connection type.
     * @param string|null $alias The alias.
     * @param bool|null $autoFields Whether the query uses auto fields.
     * @param mixed ...$options The find options.
     * @return SelectQuery The new SelectQuery instance.
     */
    public function find(
        array|string|null $fields = null,
        array|string|null $contain = null,
        array|null $join = null,
        array|string|null $conditions = null,
        array|string|null $orderBy = null,
        array|string|null $groupBy = null,
        array|string|null $having = null,
        int|null $limit = null,
        int|null $offset = null,
        string|null $epilog = null,
        string $connectionType = self::READ,
        string|null $alias = null,
        bool|null $autoFields = null,
        mixed ...$options
    ): SelectQuery {
        return $this->selectQuery([
            'fields' => $fields,
            'contain' => $contain,
            'join' => $join,
            'conditions' => $conditions,
            'orderBy' => $orderBy,
            'groupBy' => $groupBy,
            'having' => $having,
            'limit' => $limit,
            'offset' => $offset,
            'epilog' => $epilog,
            'connectionType' => $connectionType,
            'alias' => $alias,
            'autoFields' => $autoFields,
            ...$options,
        ]);
    }

    /**
     * Retrieves a single Entity.
     *
     * @param array<int|string>|int|string $primaryValues The primary key values.
     * @param array<mixed>|string|null $fields The SELECT fields.
     * @param array<mixed>|string|null $contain The contain relationships.
     * @param array<array<string, mixed>>|null $join The JOIN tables.
     * @param array<mixed>|string|null $conditions The WHERE conditions.
     * @param array<string>|string|null $orderBy The ORDER BY fields.
     * @param string|string[]|null $groupBy The GROUP BY fields.
     * @param array<mixed>|string|null $having The HAVING conditions.
     * @param int|null $limit The LIMIT clause.
     * @param int|null $offset The OFFSET clause.
     * @param string|null $epilog The epilog.
     * @param string $connectionType The connection type.
     * @param string|null $alias The alias.
     * @param bool|null $autoFields Whether the query uses auto fields.
     * @param mixed ...$options The find options.
     * @return Entity|null The Entity instance.
     */
    public function get(
        array|int|string $primaryValues,
        array|string|null $fields = null,
        array|string|null $contain = null,
        array|null $join = null,
        array|string|null $conditions = null,
        array|string|null $orderBy = null,
        array|string|null $groupBy = null,
        array|string|null $having = null,
        int|null $limit = null,
        int|null $offset = null,
        string|null $epilog = null,
        string $connectionType = self::READ,
        string|null $alias = null,
        bool|null $autoFields = null,
        mixed ...$options
    ): Entity|null {
        $primaryKeys = array_map(
            $this->aliasField(...),
            $this->getPrimaryKey()
        );
        $primaryConditions = QueryGenerator::combineConditions($primaryKeys, (array) $primaryValues);

        return $this->find(
            $fields,
            $contain,
            $join,
            $conditions,
            $orderBy,
            $groupBy,
            $having,
            $limit,
            $offset,
            $epilog,
            $connectionType,
            $alias,
            $autoFields,
            ...$options
        )->where($primaryConditions)->first();
    }

    /**
     * Returns the model alias.
     *
     * @return string The model alias.
     */
    public function getAlias(): string
    {
        return $this->alias ??= $this->getClassAlias();
    }

    /**
     * Returns the table auto increment column.
     *
     * @return string|null The table auto increment column.
     */
    public function getAutoIncrementKey(): string|null
    {
        if (!$this->autoIncrementKey) {
            $schema = $this->getSchema();

            foreach ($this->getPrimaryKey() as $key) {
                $column = $schema->column($key);

                if (!$column->isAutoIncrement()) {
                    continue;
                }

                $this->autoIncrementKey = $key;
                break;
            }
        }

        return $this->autoIncrementKey;
    }

    /**
     * Returns the model class alias.
     *
     * @return string The model class alias.
     */
    public function getClassAlias(): string
    {
        return $this->classAlias ??= (string) preg_replace('/Model$/', '', new ReflectionClass($this)->getShortName());
    }

    /**
     * Returns the Connection.
     *
     * @param string $type The connection type.
     * @return Connection The Connection instance.
     */
    public function getConnection(string $type = self::WRITE): Connection
    {
        if (!isset($this->connections[$type]) && !isset($this->connectionKeys[$type])) {
            $type = static::WRITE;
        }

        return $this->connections[$type] ??= $this->connectionManager->use($this->connectionKeys[$type] ?? $this->connectionKeys[static::WRITE]);
    }

    /**
     * Returns the display name.
     *
     * @return string The display name.
     */
    public function getDisplayName(): string
    {
        if (!$this->displayName) {
            $testColumns = array_merge(['name', 'title', 'label'], $this->getPrimaryKey());
            $columns = $this->getSchema()->columnNames();
            $matching = array_intersect($testColumns, $columns);

            $this->displayName = array_first($matching);
        }

        return (string) $this->displayName;
    }

    /**
     * Returns the primary key(s).
     *
     * @return string[] The primary key(s).
     */
    public function getPrimaryKey(): array
    {
        return $this->primaryKey ??= $this->getSchema()->primaryKey() ?? [];
    }

    /**
     * Gets a Relationship.
     *
     * @param string $name The relationship name.
     * @return Relationship|null The Relationship instance, or null if it does not exist.
     */
    public function getRelationship(string $name): Relationship|null
    {
        return $this->relationships[$name] ?? null;
    }

    /**
     * Gets all relationships.
     *
     * @return array<string, Relationship> The relationships.
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }

    /**
     * Returns the route key.
     *
     * @return string The route key.
     */
    public function getRouteKey(): string
    {
        if (!$this->routeKey) {
            $testColumns = array_merge(['slug'], $this->getPrimaryKey());
            $columns = $this->getSchema()->columnNames();
            $matching = array_intersect($testColumns, $columns);

            $this->routeKey = array_first($matching);
        }

        return (string) $this->routeKey;
    }

    /**
     * Returns the model RuleSet.
     *
     * @return RuleSet The RuleSet instance.
     */
    public function getRules(): RuleSet
    {
        return $this->rules ??= $this->buildRules($this->container->build(RuleSet::class, ['model' => $this]));
    }

    /**
     * Returns the schema Table.
     *
     * @param string $type The connection type.
     * @return Table The Table instance.
     */
    public function getSchema(string $type = self::WRITE): Table
    {
        return $this->schemaRegistry->use($this->getConnection($type))
            ->table($this->getTable());
    }

    /**
     * Returns the table name.
     *
     * @return string The table name.
     */
    public function getTable(): string
    {
        return $this->table ??= $this->inflector->underscore($this->getClassAlias());
    }

    /**
     * Returns the model Validator.
     *
     * @return Validator The Validator instance.
     */
    public function getValidator(): Validator
    {
        return $this->validator ??= $this->buildValidation($this->container->build(Validator::class));
    }

    /**
     * Creates a "has many" relationship.
     *
     * @param string $name The relationship name.
     * @param array<string, mixed> $options The relationship options.
     * @return HasMany The new HasMany instance.
     */
    public function hasMany(string $name, array $options = []): HasMany
    {
        $options['source'] = $this;

        $relationship = $this->container->build(HasMany::class, ['name' => $name, 'options' => $options]);

        $this->addRelationship($relationship);

        return $relationship;
    }

    /**
     * Creates a "has one" relationship.
     *
     * @param string $name The relationship name.
     * @param array<string, mixed> $options The relationship options.
     * @return HasOne The new HasOne instance.
     */
    public function hasOne(string $name, array $options = []): HasOne
    {
        $options['source'] = $this;

        $relationship = $this->container->build(HasOne::class, ['name' => $name, 'options' => $options]);

        $this->addRelationship($relationship);

        return $relationship;
    }

    /**
     * Checks whether a Relationship exists.
     *
     * @param string $name The relationship name.
     * @return bool Whether the Relationship exists.
     */
    public function hasRelationship(string $name): bool
    {
        return isset($this->relationships[$name]);
    }

    /**
     * Initializes the Model.
     */
    public function initialize(): void {}

    /**
     * Creates a new InsertQuery.
     *
     * @return InsertQuery The new InsertQuery instance.
     */
    public function insertQuery(): InsertQuery
    {
        return new InsertQuery($this);
    }

    /**
     * Loads contained data into an Entity.
     *
     * @param Entity $entity The Entity.
     * @param array<mixed> $contain The relationships to contain.
     * @return Entity|null The Entity instance.
     */
    public function loadInto(Entity $entity, array $contain): Entity|null
    {
        $primaryValues = $this->getPrimaryKey() |> $entity->extract(...);

        $tempEntity = $this->get($primaryValues, contain: $contain, autoFields: false);

        if (!$tempEntity) {
            return $entity;
        }

        foreach ($this->relationships as $relationship) {
            $property = $relationship->getProperty();

            if (!$tempEntity->has($property)) {
                continue;
            }

            $value = $tempEntity->get($property);

            $entity
                ->set($property, $value)
                ->setDirty($property, false);
        }

        return $entity;
    }

    /**
     * Creates a "many to many" relationship.
     *
     * @param string $name The relationship name.
     * @param array<string, mixed> $options The relationship options.
     * @return ManyToMany The new ManyToMany instance.
     */
    public function manyToMany(string $name, array $options = []): ManyToMany
    {
        $options['source'] = $this;

        $relationship = $this->container->build(ManyToMany::class, ['name' => $name, 'options' => $options]);

        $this->addRelationship($relationship);

        return $relationship;
    }

    /**
     * Builds a new empty Entity.
     *
     * @return Entity The new Entity instance.
     */
    public function newEmptyEntity(): Entity
    {
        return $this->createEntity();
    }

    /**
     * Builds multiple new Entity instances using data.
     *
     * @param array<string, mixed>[] $data The data.
     * @param array<mixed>|string|null $associated The associated relationships.
     * @param array<string, bool>|null $accessible The accessible fields.
     * @param bool $guard Whether to check field accessibility.
     * @param bool $mutate Whether to allow mutations.
     * @param bool $validate Whether to validate the data.
     * @param bool $parse Whether to parse the DB schema.
     * @param bool $events Whether to trigger events.
     * @param bool $clean Whether to clean the entity.
     * @param bool|null $new Whether the entity is new.
     * @param mixed ...$options The Entity options.
     * @return Entity[] The new Entity instances.
     */
    public function newEntities(
        array $data,
        array|string|null $associated = null,
        array|null $accessible = null,
        bool $guard = true,
        bool $mutate = true,
        bool $validate = true,
        bool $parse = true,
        bool $events = true,
        bool $clean = false,
        bool|null $new = null,
        mixed ...$options
    ): array {
        return array_map(
            fn(array $values): Entity => $this->newEntity(
                $values,
                $associated,
                $accessible,
                $guard,
                $mutate,
                $validate,
                $parse,
                $events,
                $clean,
                $new,
                ...$options
            ),
            $data
        );
    }

    /**
     * Builds a new Entity using data.
     *
     * @param array<string, mixed> $data The data.
     * @param array<mixed>|string|null $associated The associated relationships.
     * @param array<string, bool>|null $accessible The accessible fields.
     * @param bool $guard Whether to check field accessibility.
     * @param bool $mutate Whether to allow mutations.
     * @param bool $validate Whether to validate the data.
     * @param bool $parse Whether to parse the DB schema.
     * @param bool $events Whether to trigger events.
     * @param bool $clean Whether to clean the entity.
     * @param bool|null $new Whether the entity is new.
     * @param mixed ...$options The Entity options.
     * @return Entity The new Entity instance.
     */
    public function newEntity(
        array $data,
        array|string|null $associated = null,
        array|null $accessible = null,
        bool $guard = true,
        bool $mutate = true,
        bool $validate = true,
        bool $parse = true,
        bool $events = true,
        bool $clean = false,
        bool|null $new = null,
        mixed ...$options
    ): Entity {
        $entity = $this->createEntity();

        $this->injectInto($entity, $data, [
            'associated' => $associated,
            'accessible' => $accessible,
            'guard' => $guard,
            'mutate' => $mutate,
            'validate' => $validate,
            'parse' => $parse,
            'events' => $events,
            'clean' => $clean,
            'new' => $new,
            ...$options,
        ]);

        return $entity;
    }

    /**
     * Parses data from the user.
     *
     * @param array<string, mixed> $data The data.
     * @return array<string, mixed> The user values.
     */
    public function parseSchema(array $data): array
    {
        $schema = $this->getSchema();

        foreach ($data as $field => $value) {
            if (!$schema->hasColumn($field)) {
                continue;
            }

            $column = $schema->column($field);
            $value = $column->type()->parse($value);
            $enumClass = $column->getEnumClass();

            $data[$field] = $enumClass ?
                EnumHelper::parseValue($enumClass, $value) :
                $value;
        }

        return $data;
    }

    /**
     * Updates multiple Entity instances using data.
     *
     * @param iterable<Entity> $entities The entities.
     * @param array<array<string, mixed>> $data The data.
     * @param array<mixed>|string|null $associated The associated relationships.
     * @param array<string, bool>|null $accessible The accessible fields.
     * @param bool $guard Whether to check field accessibility.
     * @param bool $mutate Whether to allow mutations.
     * @param bool $validate Whether to validate the data.
     * @param bool $parse Whether to parse the DB schema.
     * @param bool $events Whether to trigger events.
     * @param bool $clean Whether to clean the entity.
     * @param bool|null $new Whether the entity is new.
     * @param mixed ...$options The Entity options.
     */
    public function patchEntities(
        array|Traversable $entities,
        array $data,
        array|string|null $associated = null,
        array|null $accessible = null,
        bool $guard = true,
        bool $mutate = true,
        bool $validate = true,
        bool $parse = true,
        bool $events = true,
        bool $clean = false,
        bool|null $new = null,
        mixed ...$options
    ): void {
        foreach ($entities as $i => $entity) {
            if (!isset($data[$i])) {
                continue;
            }

            $this->patchEntity(
                $entity,
                $data[$i],
                $associated,
                $accessible,
                $guard,
                $mutate,
                $validate,
                $parse,
                $events,
                $clean,
                $new,
                ...$options
            );
        }
    }

    /**
     * Updates an Entity using data.
     *
     * @param Entity $entity The Entity.
     * @param array<string, mixed> $data The data.
     * @param array<mixed>|string|null $associated The associated relationships.
     * @param array<string, bool>|null $accessible The accessible fields.
     * @param bool $guard Whether to check field accessibility.
     * @param bool $mutate Whether to allow mutations.
     * @param bool $validate Whether to validate the data.
     * @param bool $parse Whether to parse the DB schema.
     * @param bool $events Whether to trigger events.
     * @param bool $clean Whether to clean the entity.
     * @param bool|null $new Whether the entity is new.
     * @param mixed ...$options The Entity options.
     */
    public function patchEntity(
        Entity $entity,
        array $data,
        array|string|null $associated = null,
        array|null $accessible = null,
        bool $guard = true,
        bool $mutate = true,
        bool $validate = true,
        bool $parse = true,
        bool $events = true,
        bool $clean = false,
        bool|null $new = null,
        mixed ...$options
    ): void {
        $this->injectInto($entity, $data, [
            'associated' => $associated,
            'accessible' => $accessible,
            'guard' => $guard,
            'mutate' => $mutate,
            'validate' => $validate,
            'parse' => $parse,
            'events' => $events,
            'clean' => $clean,
            'new' => $new,
            ...$options,
        ]);
    }

    /**
     * Removes an existing Relationship.
     *
     * @param string $name The relationship name.
     * @return static The Model instance.
     */
    public function removeRelationship(string $name): static
    {
        unset($this->relationships[$name]);

        return $this;
    }

    /**
     * Resolves an Entity from a route.
     *
     * @param int|string $value The value.
     * @param string $field The field.
     * @param Entity|null $parent The parent Entity.
     * @return Entity|null The Entity instance.
     */
    public function resolveRouteBinding(int|string $value, string $field, Entity|null $parent = null): Entity|null
    {
        $query = $this->find()
            ->where([
                $this->aliasField($field) => $value,
            ]);

        if ($parent) {
            $source = (string) $parent->getSource();
            $relationship = $this->getRelationship($source);

            if ($relationship) {
                $target = $relationship->getTarget();

                $primaryKeys = $target->getPrimaryKey();

                $targetFields = array_map(
                    $target->aliasField(...),
                    $primaryKeys
                );

                $primaryValues = $parent->extract($primaryKeys);
                $conditions = QueryGenerator::combineConditions($targetFields, $primaryValues);

                $query->innerJoinWith($source, $conditions);
            }
        }

        return $query->first();
    }

    /**
     * Saves an Entity.
     *
     * @param Entity $entity The Entity.
     * @param bool $saveRelated Whether to save related entities.
     * @param bool $checkRules Whether to check model RuleSet.
     * @param bool $checkExists Whether to check if the entity exists.
     * @param bool $events Whether to trigger events.
     * @param bool $clean Whether to clean the entity.
     * @param mixed ...$options The save options.
     * @return bool Whether the save was successful.
     */
    public function save(
        Entity $entity,
        bool $saveRelated = true,
        bool $checkRules = true,
        bool $checkExists = true,
        bool $events = true,
        bool $clean = true,
        mixed ...$options
    ): bool {
        if (!$entity->isNew() && !$entity->isDirty()) {
            return true;
        }

        if ($entity->hasErrors()) {
            return false;
        }

        $options['saveRelated'] = $saveRelated;
        $options['checkRules'] = $checkRules;
        $options['checkExists'] = $checkExists;
        $options['events'] = $events;
        $options['clean'] = $clean;

        if ($checkExists) {
            $this->checkExists([$entity]);
        }

        $connection = $this->getConnection();

        $connection->begin();

        if (!$this->performSave($entity, $options)) {
            $connection->rollback();

            static::resetParents([$entity], $this);
            static::resetChildren([$entity], $this);

            $entity->clearTemporaryFields();

            return false;
        }

        if ($events) {
            $connection->afterCommit(function() use ($entity, $options): void {
                $this->dispatchEvent('ORM.afterSaveCommit', ['entity' => $entity, 'options' => $options]);
            }, 100);
        }

        if ($clean) {
            $connection->afterCommit(function() use ($entity): void {
                static::cleanEntities([$entity], $this);
            }, 200);
        }

        $connection->commit();

        return true;
    }

    /**
     * Saves multiple entities.
     *
     * @param iterable<Entity> $entities The entities.
     * @param bool $saveRelated Whether to save related entities.
     * @param bool $checkRules Whether to check model RuleSet.
     * @param bool $checkExists Whether to check if the entity exists.
     * @param bool $events Whether to trigger events.
     * @param bool $clean Whether to clean the entity.
     * @param mixed ...$options The save options.
     * @return bool Whether the save was successful.
     */
    public function saveMany(
        array|Traversable $entities,
        bool $saveRelated = true,
        bool $checkRules = true,
        bool $checkExists = true,
        bool $events = true,
        bool $clean = true,
        mixed ...$options
    ): bool {
        if (!is_array($entities)) {
            $entities = iterator_to_array($entities);
        }

        $entities = array_filter(
            $entities,
            static fn(Entity $entity): bool => $entity->isNew() || $entity->isDirty()
        );

        if ($entities === []) {
            return true;
        }

        static::checkEntities($entities);

        $options['saveRelated'] = $saveRelated;
        $options['checkRules'] = $checkRules;
        $options['checkExists'] = $checkExists;
        $options['events'] = $events;
        $options['clean'] = $clean;

        if (count($entities) === 1) {
            return $this->save($entities[0], ...$options);
        }

        foreach ($entities as $entity) {
            if ($entity->hasErrors()) {
                return false;
            }
        }

        if ($checkExists) {
            $this->checkExists($entities);
        }

        $connection = $this->getConnection();

        $connection->begin();

        $result = true;
        foreach ($entities as $entity) {
            if (!$this->performSave($entity, $options)) {
                $result = false;
                break;
            }
        }

        if (!$result) {
            $connection->rollback();

            static::resetParents($entities, $this);
            static::resetChildren($entities, $this);

            foreach ($entities as $entity) {
                $entity->clearTemporaryFields();
            }

            return false;
        }

        if ($events) {
            $connection->afterCommit(function() use ($entities, $options): void {
                foreach ($entities as $entity) {
                    $this->dispatchEvent('ORM.afterSaveCommit', ['entity' => $entity, 'options' => $options]);
                }
            }, 100);
        }

        if ($clean) {
            $connection->afterCommit(function() use ($entities): void {
                static::cleanEntities($entities, $this);
            }, 200);
        }

        $connection->commit();

        return true;
    }

    /**
     * Creates a new SelectQuery.
     *
     * @param array<mixed> $options The options for the query.
     * @return SelectQuery The new SelectQuery instance.
     */
    public function selectQuery(array $options = []): SelectQuery
    {
        return new SelectQuery($this, $options);
    }

    /**
     * Sets the model alias.
     *
     * @param string $alias The model alias.
     * @return static The Model instance.
     */
    public function setAlias(string $alias): static
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Sets the model class alias.
     *
     * @param string $classAlias The model class alias.
     * @return static The Model instance.
     */
    public function setClassAlias(string $classAlias): static
    {
        $this->classAlias = $classAlias;

        return $this;
    }

    /**
     * Sets the Connection.
     *
     * @param Connection $connection The Connection.
     * @param string $type The connection type.
     * @return static The Model instance.
     */
    public function setConnection(Connection $connection, string $type = self::WRITE): static
    {
        $this->connections[$type] = $connection;

        return $this;
    }

    /**
     * Sets the display name.
     *
     * @param string $displayName The display name.
     * @return static The Model instance.
     */
    public function setDisplayName(string $displayName): static
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Sets the model RuleSet.
     *
     * @param RuleSet $rules The RuleSet.
     * @return static The Model instance.
     */
    public function setRules(RuleSet $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * Sets the table name.
     *
     * @param string $table The table name.
     * @return static The Model instance.
     */
    public function setTable(string $table): static
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Sets the model Validator.
     *
     * @param Validator $validator The Validator.
     * @return static The Model instance.
     */
    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Creates a new subquery SelectQuery.
     *
     * @param array<mixed>|string|null $fields The SELECT fields.
     * @param array<mixed>|string|null $contain The contain relationships.
     * @param array<array<string, mixed>>|null $join The JOIN tables.
     * @param array<mixed>|string|null $conditions The WHERE conditions.
     * @param array<string>|string|null $orderBy The ORDER BY fields.
     * @param string|string[]|null $groupBy The GROUP BY fields.
     * @param array<mixed>|string|null $having The HAVING conditions.
     * @param int|null $limit The LIMIT clause.
     * @param int|null $offset The OFFSET clause.
     * @param string|null $epilog The epilog.
     * @param string $connectionType The connection type.
     * @param string|null $alias The alias.
     * @param bool|null $autoFields Whether the query uses auto fields.
     * @param mixed ...$options The find options.
     * @return SelectQuery The new SelectQuery instance.
     */
    public function subquery(
        array|string|null $fields = null,
        array|string|null $contain = null,
        array|null $join = null,
        array|string|null $conditions = null,
        array|string|null $orderBy = null,
        array|string|null $groupBy = null,
        array|string|null $having = null,
        int|null $limit = null,
        int|null $offset = null,
        string|null $epilog = null,
        string $connectionType = self::READ,
        string|null $alias = null,
        bool|null $autoFields = null,
        mixed ...$options
    ): SelectQuery {
        return $this->selectQuery([
            'fields' => $fields,
            'contain' => $contain,
            'join' => $join,
            'conditions' => $conditions,
            'orderBy' => $orderBy,
            'groupBy' => $groupBy,
            'having' => $having,
            'limit' => $limit,
            'offset' => $offset,
            'epilog' => $epilog,
            'connectionType' => $connectionType,
            'alias' => $alias,
            'autoFields' => $autoFields,
            'subquery' => true,
            ...$options,
        ]);
    }

    /**
     * Converts data to database values.
     *
     * @param array<string, mixed> $data The data.
     * @return array<string, mixed> The database values.
     */
    public function toDatabaseSchema(array $data): array
    {
        $schema = $this->getSchema();

        foreach ($data as $field => $value) {
            if (!$schema->hasColumn($field)) {
                continue;
            }

            $column = $schema->column($field);
            $value = EnumHelper::normalizeValue($value);
            $data[$field] = $column->type()->toDatabase($value);
        }

        return $data;
    }

    /**
     * Updates all rows matching conditions.
     *
     * @param array<string, mixed> $data The data to update.
     * @param array<mixed> $conditions The conditions.
     * @return int The number of rows affected.
     */
    public function updateAll(array $data, array $conditions): int
    {
        return $this->updateQuery()
            ->set($data)
            ->where($conditions)
            ->execute()
            ->count();
    }

    /**
     * Creates a new UpdateBatchQuery.
     *
     * @return UpdateBatchQuery The new UpdateBatchQuery instance.
     */
    public function updateBatchQuery(): UpdateBatchQuery
    {
        return new UpdateBatchQuery($this);
    }

    /**
     * Creates a new UpdateQuery.
     *
     * @return UpdateQuery The new UpdateQuery instance.
     */
    public function updateQuery(): UpdateQuery
    {
        return new UpdateQuery($this);
    }

    /**
     * Creates a new UpsertQuery.
     *
     * @param string|string[]|null $conflictKeys The conflict keys.
     * @return UpsertQuery The new UpsertQuery instance.
     */
    public function upsertQuery(array|string|null $conflictKeys = null): UpsertQuery
    {
        return new UpsertQuery($this, $conflictKeys);
    }

    /**
     * Determines whether entities already exist, and marks them not new.
     *
     * @param Entity[] $entities The entities.
     */
    protected function checkExists(array $entities): void
    {
        $primaryKeys = $this->getPrimaryKey();

        $entities = array_filter(
            array_values($entities),
            static fn(Entity $entity): bool => $entity->isNew() && $entity->extractDirty($primaryKeys) !== []
        );

        if ($entities === []) {
            return;
        }

        $values = array_map(
            static fn(Entity $entity): array => $entity->extract($primaryKeys),
            $entities
        );

        $primaryKeys = array_map(
            $this->aliasField(...),
            $primaryKeys
        );

        $matchedValues = $this->find(
            fields: $primaryKeys,
            conditions: QueryGenerator::normalizeConditions($primaryKeys, $values),
            events: false,
        )
            ->getResult()
            ->map(static function(Entity|null $entity) use ($primaryKeys): array {
                assert($entity instanceof Entity);

                return $entity->extract($primaryKeys);
            })
            ->toArray();

        if ($matchedValues === []) {
            return;
        }

        foreach ($values as $i => $data) {
            foreach ($matchedValues as $other) {
                if (array_diff_assoc($data, $other) === []) {
                    continue;
                }

                $entities[$i]->setNew(false);
                break;
            }
        }
    }

    /**
     * Creates an Entity.
     *
     * @return Entity The Entity instance.
     */
    protected function createEntity(): Entity
    {
        $alias = $this->getClassAlias();

        $className = $this->entityLocator->find($alias);

        $entity = $this->container->build($className);

        return $entity->setSource($alias);
    }

    /**
     * Deletes child entities.
     *
     * @param Entity[] $entities The entities.
     * @param array<string, mixed> $options The delete options.
     * @return bool Whether the delete was successful.
     */
    protected function deleteChildren(array $entities, array $options): bool
    {
        foreach ($this->relationships as $relationship) {
            if (!$relationship->isOwningSide()) {
                continue;
            }

            if (!$relationship->unlinkAll($entities, ...$options)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Injects an Entity with data.
     *
     * @param Entity $entity The Entity.
     * @param array<string, mixed> $data The data.
     * @param array<mixed> $options The Entity options.
     */
    protected function injectInto(Entity $entity, array $data, array $options): void
    {
        $options['associated'] ??= null;
        $options['accessible'] ??= null;
        $options['guard'] ??= true;
        $options['mutate'] ??= true;
        $options['parse'] ??= true;
        $options['events'] ??= true;
        $options['validate'] ??= true;
        $options['clean'] ??= false;
        $options['new'] ??= null;

        $schema = $this->getSchema();

        if ($options['parse']) {
            if ($options['events']) {
                $data = new ArrayObject($data);
                $this->dispatchEvent('ORM.beforeParse', ['data' => $data, 'options' => $options]);
                $data = $data->getArrayCopy();
            }

            $data = $this->parseSchema($data);
        }

        $associated = null;
        if ($options['associated'] !== null) {
            $associated = static::normalizeContain($options['associated'], $this, 'associated')['associated'];
        }

        $accessible = null;
        if ($options['accessible'] && $options['guard']) {
            $accessible = $entity->getAccessible();
            foreach ($options['accessible'] as $field => $access) {
                $entity->setAccess($field, $access);
            }
        }

        $errors = [];
        if ($options['validate']) {
            $type = $entity->isNew() ? 'create' : 'update';

            $validationData = $options['guard'] ?
                array_filter(
                    $data,
                    $entity->isAccessible(...),
                    ARRAY_FILTER_USE_KEY
                ) :
                $data;

            $validator = $this->getValidator();
            $errors = $validator->validate($validationData, $type);

            $entity->setErrors($errors);
        }

        $relationships = [];
        foreach ($this->relationships as $relationship) {
            $alias = $relationship->getName();
            $property = $relationship->getProperty();

            if ($associated !== null && !isset($associated[$alias])) {
                $relationships[$property] = false;

                continue;
            }

            $relationships[$property] = $alias;
        }

        foreach ($data as $field => $value) {
            if (isset($errors[$field])) {
                $entity->setInvalid($field, $value);

                continue;
            }

            if (!is_array($value) || $schema->hasColumn($field) || !isset($relationships[$field])) {
                $entity->set($field, $value, $options['guard'], $options['mutate']);

                continue;
            }

            if (!$relationships[$field]) {
                continue;
            }

            $alias = $relationships[$field];
            $relationship = $this->getRelationship($alias);

            assert($relationship instanceof Relationship);

            $relationOptions = array_merge(
                $options,
                [
                    'associated' => [],
                    'onlyIds' => false,
                ],
                $associated[$alias] ?? []
            );

            if (!$relationship->hasMultiple()) {
                static::injectSingleRelation($entity, $relationship, $value, $relationOptions);
            } else if ($relationOptions['onlyIds']) {
                static::injectMultipleRelationsFromIds($entity, $relationship, $value);
            } else {
                static::injectMultipleRelations($entity, $relationship, $value, $relationOptions);
            }
        }

        if ($accessible !== null) {
            foreach ($accessible as $field => $access) {
                $entity->setAccess($field, $access);
            }
        }

        if ($options['new'] !== null) {
            $entity->setNew($options['new']);
        }

        if ($options['clean']) {
            $entity->clean();
        }

        if ($options['events'] && $options['parse']) {
            $this->dispatchEvent('ORM.afterParse', ['entity' => $entity, 'options' => $options]);
        }
    }

    /**
     * Deletes a single Entity.
     *
     * @param Entity $entity The Entity.
     * @param array<string, mixed> $options The options for deleting.
     * @return bool Whether the delete was successful.
     */
    protected function performDelete(Entity $entity, array $options): bool
    {
        if ($options['events']) {
            $event = $this->dispatchEvent('ORM.beforeDelete', ['entity' => $entity, 'options' => $options]);

            if ($event->isPropagationStopped()) {
                return (bool) $event->getResult();
            }
        }

        $primaryKeys = $this->getPrimaryKey();
        $primaryValues = $entity->extract($primaryKeys);
        $conditions = QueryGenerator::combineConditions($primaryKeys, $primaryValues);

        if (!$this->deleteAll($conditions)) {
            return false;
        }

        if ($options['cascade'] && !$this->deleteChildren([$entity], $options)) {
            return false;
        }

        if ($options['events']) {
            $event = $this->dispatchEvent('ORM.afterDelete', ['entity' => $entity, 'options' => $options]);

            if ($event->isPropagationStopped()) {
                return (bool) $event->getResult();
            }
        }

        return true;
    }

    /**
     * Saves a single Entity.
     *
     * @param Entity $entity The Entity.
     * @param array<string, mixed> $options The options for saving.
     * @return bool Whether the save was successful.
     */
    protected function performSave(Entity $entity, array $options): bool
    {
        if ($options['checkRules']) {
            if ($options['events']) {
                $event = $this->dispatchEvent('ORM.beforeRules', ['entity' => $entity, 'options' => $options]);

                if ($event->isPropagationStopped()) {
                    return (bool) $event->getResult();
                }
            }

            if (!$this->getRules()->validate($entity)) {
                return false;
            }

            if ($options['events']) {
                $event = $this->dispatchEvent('ORM.afterRules', ['entity' => $entity, 'options' => $options]);

                if ($event->isPropagationStopped()) {
                    return (bool) $event->getResult();
                }
            }
        }

        if ($options['events']) {
            $event = $this->dispatchEvent('ORM.beforeSave', ['entity' => $entity, 'options' => $options]);

            if ($event->isPropagationStopped()) {
                return (bool) $event->getResult();
            }
        }

        if ($options['saveRelated'] && !$this->saveParents($entity, $options)) {
            return false;
        }

        $schema = $this->getSchema();
        $columns = $schema->columnNames();
        $primaryKeys = $this->getPrimaryKey();
        $autoIncrementKey = $this->getAutoIncrementKey();

        $data = $entity->extractDirty($columns) |> $this->toDatabaseSchema(...);

        if ($entity->isNew()) {
            $newData = $this->insertQuery()
                ->values([$data])
                ->execute()
                ->fetch() ?? [];

            foreach ($primaryKeys as $primaryKey) {
                $primaryKey = (string) $primaryKey;

                if ($entity->hasValue($primaryKey)) {
                    continue;
                }

                if (array_key_exists($primaryKey, $newData)) {
                    $value = $newData[$primaryKey];
                } else if ($primaryKey === $autoIncrementKey) {
                    $value = $this->getConnection()->insertId();
                } else {
                    continue;
                }

                $value = $schema->column($primaryKey)
                    ->type()
                    ->parse($value);

                $entity->set($primaryKey, $value, temporary: true);
            }
        } else if ($data !== []) {
            $primaryValues = $entity->extract($primaryKeys);
            $conditions = QueryGenerator::combineConditions($primaryKeys, $primaryValues);
            $this->updateAll($data, $conditions);
        }

        if ($options['saveRelated'] && !$this->saveChildren($entity, $options)) {
            return false;
        }

        if ($options['events']) {
            $event = $this->dispatchEvent('ORM.afterSave', ['entity' => $entity, 'options' => $options]);

            if ($event->isPropagationStopped()) {
                return (bool) $event->getResult();
            }
        }

        return true;
    }

    /**
     * Saves child entities for an entity.
     *
     * @param Entity $entity The Entity.
     * @param array<string, mixed> $options The save options.
     * @return bool Whether the save was successful.
     */
    protected function saveChildren(Entity $entity, array $options): bool
    {
        $options['clean'] = false;

        foreach ($this->relationships as $relationship) {
            if (!$relationship->isOwningSide()) {
                continue;
            }

            if (!$relationship->saveRelated($entity, ...$options)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Saves parent entities for an entity.
     *
     * @param Entity $entity The Entity.
     * @param array<string, mixed> $options The save options.
     * @return bool Whether the save was successful.
     */
    protected function saveParents(Entity $entity, array $options): bool
    {
        $options['clean'] = false;

        foreach ($this->relationships as $relationship) {
            if ($relationship->isOwningSide()) {
                continue;
            }

            if (!$relationship->saveRelated($entity, ...$options)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determines whether all entities are instances of Entity.
     *
     * @param mixed[] $entities The entities.
     *
     * @throws OrmException If an entity is not an instance of Entity.
     */
    protected static function checkEntities(array $entities): void
    {
        foreach ($entities as $entity) {
            if (!is_object($entity)) {
                throw new OrmException(sprintf(
                    'Entity `%s` must be an object and extend `%s`.',
                    gettype($entity),
                    Entity::class
                ));
            }

            if (!is_a($entity, Entity::class)) {
                throw new OrmException(sprintf(
                    'Entity `%s` must extend `%s`.',
                    $entity::class,
                    Entity::class
                ));
            }
        }
    }

    /**
     * Cleans entities recursively.
     *
     * @param Entity[] $entities The entities.
     * @param Model $model The Model.
     */
    protected static function cleanEntities(array $entities, Model $model): void
    {
        $source = $model->getAlias();
        $relationships = $model->getRelationships();

        foreach ($relationships as $relationship) {
            $property = $relationship->getProperty();

            $allRelations = [];
            foreach ($entities as $entity) {
                $relation = $entity->get($property);

                if (!$relation) {
                    continue;
                }

                if ($relationship->hasMultiple()) {
                    $allRelations = array_merge($allRelations, $relation);
                } else {
                    $allRelations[] = $relation;
                }
            }

            if ($allRelations === []) {
                continue;
            }

            $target = $relationship->getTarget();

            static::cleanEntities($allRelations, $target);
        }

        foreach ($entities as $entity) {
            $entity
                ->clean()
                ->setNew(false)
                ->setSource($source);
        }
    }

    /**
     * Injects an Entity with multiple related entities.
     *
     * @param Entity $entity The Entity.
     * @param Relationship $relationship The Relationship.
     * @param (array<string, mixed>|scalar)[] $data The data.
     * @param array<string, mixed> $options The Entity options.
     */
    protected static function injectMultipleRelations(Entity $entity, Relationship $relationship, array $data = [], array $options = []): void
    {
        $target = $relationship->getTarget();
        $targetKeys = $target->getPrimaryKey();
        $field = $relationship->getProperty();

        $firstKey = array_first($targetKeys);

        $currentRelations = $entity->get($field) ?? [];
        $relations = [];

        foreach ($data as $value) {
            if (!is_array($value)) {
                assert(is_string($firstKey));

                $value = [$firstKey => $value];
            }

            $relation = array_find(
                $currentRelations,
                static fn(mixed $currentRelation): bool => $currentRelation instanceof Entity && array_diff_assoc($currentRelation->extract($targetKeys), $value) === []
            );

            if ($relation) {
                $target->patchEntity($relation, $value, ...$options);
            } else {
                $relation = $target->newEntity($value, ...$options);
            }

            if (
                isset($value['_joinData']) &&
                is_array($value['_joinData']) &&
                $relationship instanceof ManyToMany
            ) {
                $joinRelation = $relationship->getJunction()
                    ->newEntity($value['_joinData'], ...array_merge($options, ['associated' => []]));
                $relation->set('_joinData', $joinRelation);
            }

            $relations[] = $relation;
        }

        $entity->set($field, $relations);
    }

    /**
     * Injects an Entity with multiple related entities from IDs.
     *
     * @param Entity $entity The Entity.
     * @param Relationship $relationship The Relationship.
     * @param (array<string, mixed>|scalar)[] $data The data.
     */
    protected static function injectMultipleRelationsFromIds(Entity $entity, Relationship $relationship, array $data = []): void
    {
        $target = $relationship->getTarget();
        $targetKeys = $target->getPrimaryKey();
        $field = $relationship->getProperty();

        $firstKey = array_first($targetKeys);
        $targetKeysIndex = array_flip($targetKeys);

        $targetValues = [];

        foreach ($data as $value) {
            if (!is_array($value)) {
                assert(is_string($firstKey));

                $value = [$firstKey => $value];
            }

            if (array_intersect_key($targetKeysIndex, $value) === []) {
                continue;
            }

            $targetValues[] = array_map(
                static fn(string $targetKey): mixed => $value[$targetKey] ?? null,
                $targetKeys
            );
        }

        if ($targetValues === []) {
            $entity->set($field, []);

            return;
        }

        $targetKeys = array_map(
            $target->aliasField(...),
            $targetKeys
        );

        $targetConditions = QueryGenerator::normalizeConditions($targetKeys, $targetValues);

        $relations = $target->find()
            ->where($targetConditions)
            ->all()
            ->toArray();

        $entity->set($field, $relations);
    }

    /**
     * Injects an Entity with a single related entity.
     *
     * @param Entity $entity The Entity.
     * @param Relationship $relationship Relationship The Relationship.
     * @param array<string, mixed> $data The data.
     * @param array<string, mixed> $options The Entity options.
     */
    protected static function injectSingleRelation(Entity $entity, Relationship $relationship, array $data = [], array $options = []): void
    {
        $target = $relationship->getTarget();
        $targetKeys = $target->getPrimaryKey();
        $field = $relationship->getProperty();

        $relation = $entity->get($field);

        if ($relation && $relation instanceof Entity && array_diff_assoc($relation->extract($targetKeys), $data) === []) {
            $target->patchEntity($relation, $data, ...$options);
        } else {
            $relation = $target->newEntity($data, ...$options);
        }

        $entity->set($field, $relation);
    }

    /**
     * Resets child entities.
     *
     * @param Entity[] $entities The entities.
     * @param Model $model The Model.
     */
    protected static function resetChildren(array $entities, Model $model): void
    {
        $relationships = $model->getRelationships();

        foreach ($relationships as $relationship) {
            if (!$relationship->isOwningSide()) {
                continue;
            }

            $target = $relationship->getTarget();
            $property = $relationship->getProperty();

            $allChildren = [];
            foreach ($entities as $entity) {
                $children = $entity->get($property);

                if (!$children) {
                    continue;
                }

                if ($relationship->hasMultiple()) {
                    $allChildren = array_merge($allChildren, $children);
                } else {
                    $allChildren[] = $children;
                }
            }

            if ($allChildren !== []) {
                static::resetChildren($allChildren, $target);
            }

            foreach ($allChildren as $child) {
                $child->clearTemporaryFields();

                if ($relationship instanceof ManyToMany && $child->hasValue('_joinData')) {
                    $child->get('_joinData')->clearTemporaryFields();
                }
            }
        }
    }

    /**
     * Resets parent entities.
     *
     * @param Entity[] $entities The entities.
     * @param Model $model The Model.
     */
    protected static function resetParents(array $entities, Model $model): void
    {
        $relationships = $model->getRelationships();

        foreach ($relationships as $relationship) {
            if ($relationship->isOwningSide()) {
                continue;
            }

            $target = $relationship->getTarget();
            $property = $relationship->getProperty();

            $allParents = [];
            foreach ($entities as $entity) {
                $parent = $entity->get($property);

                if (!$parent) {
                    continue;
                }

                $allParents[] = $parent;
            }

            if ($allParents !== []) {
                static::resetParents($allParents, $target);
            }

            foreach ($allParents as $parent) {
                $parent->clearTemporaryFields();
            }
        }
    }
}

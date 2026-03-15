<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Fixture;

use Fyre\Core\Traits\DebugTrait;
use Fyre\ORM\Model;
use Fyre\ORM\ModelRegistry;
use Fyre\ORM\Relationship;
use Fyre\ORM\Relationships\ManyToMany;
use ReflectionClass;
use RuntimeException;

use function array_keys;
use function assert;
use function preg_replace;
use function sprintf;

/**
 * Provides a base class for test fixtures.
 *
 * Fixtures define a set of rows to be inserted for a model. The default model is resolved
 * from the fixture class name (stripping the `Fixture` suffix).
 */
abstract class Fixture
{
    use DebugTrait;

    /**
     * @var array<mixed>|string|null
     */
    protected array|string|null $associated = [];

    protected string $classAlias;

    /**
     * @var iterable<mixed>
     */
    protected iterable $data = [];

    protected Model $model;

    protected ModelRegistry $modelRegistry;

    /**
     * Constructs a Fixture.
     *
     * @param ModelRegistry $modelRegistry The ModelRegistry.
     */
    public function __construct(ModelRegistry $modelRegistry)
    {
        $this->modelRegistry = $modelRegistry;
    }

    /**
     * Returns the fixture associations.
     *
     * @return array<mixed>|string|null The associated relationships.
     */
    public function associated(): array|string|null
    {
        return $this->associated;
    }

    /**
     * Returns the fixture data.
     *
     * @return iterable<mixed> The fixture data.
     */
    public function data(): iterable
    {
        return $this->data;
    }

    /**
     * Returns the class alias for the fixture.
     *
     * @return string The class alias.
     */
    public function getClassAlias(): string
    {
        return $this->classAlias ??= (string) preg_replace('/Fixture$/', '', new ReflectionClass(static::class)->getShortName());
    }

    /**
     * Returns the Model for the fixture.
     *
     * @return Model The Model instance.
     */
    public function getModel(): Model
    {
        return $this->model ??= $this->getClassAlias() |> $this->modelRegistry->use(...);
    }

    /**
     * Returns all tables implied by the fixture and its configured associations.
     *
     * @return string[] The table names.
     */
    public function getTables(): array
    {
        $model = $this->getModel();
        $associated = Model::normalizeContain($this->associated() ?? [], $model, 'associated')['associated'];

        $tables = [$model->getTable() => true];

        $collect = function(Model $model, array $associated) use (&$collect, &$tables): void {
            foreach ($associated as $alias => $data) {
                $relationship = $model->getRelationship($alias);

                assert($relationship instanceof Relationship);

                if ($relationship instanceof ManyToMany) {
                    $tables[$relationship->getJunction()->getTable()] = true;
                }

                $target = $relationship->getTarget();
                $tables[$target->getTable()] = true;

                $collect($target, $data['associated']);
            }
        };

        $collect($model, $associated);

        return array_keys($tables);
    }

    /**
     * Loads the fixture data.
     *
     * Note: Entities are created with `guard: false` and `validate: false`, and are saved
     * without existence/rule checks.
     *
     * @throws RuntimeException If an entity cannot be saved.
     */
    public function run(): void
    {
        $model = $this->getModel();
        $data = $this->data();
        $associated = $this->associated();

        foreach ($data as $i => $row) {
            $entity = $model->newEntity($row, $associated, guard: false, validate: false);

            if (!$model->save($entity, checkExists: false, checkRules: false)) {
                throw new RuntimeException(sprintf(
                    'Fixture entity #%d for `%s` could not be saved.',
                    $i,
                    $model->getAlias()
                ));
            }
        }
    }
}

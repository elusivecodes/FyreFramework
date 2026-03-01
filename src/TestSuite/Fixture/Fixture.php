<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Fixture;

use Fyre\Core\Traits\DebugTrait;
use Fyre\ORM\Model;
use Fyre\ORM\ModelRegistry;
use ReflectionClass;
use RuntimeException;

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
        return $this->model ??= $this->modelRegistry->use($this->getClassAlias());
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

        foreach ($data as $i => $row) {
            $entity = $model->newEntity($row, guard: false, validate: false);

            if (!$model->save($entity, checkExists: false, checkRules: false)) {
                throw new RuntimeException(sprintf(
                    'Fixture entity #%d for `%s` could not be saved.',
                    $i,
                    $model->getAlias()
                ));
            }
        }
    }

    /**
     * Truncates the fixture table.
     *
     * Note: This uses the model connection to truncate the underlying table.
     */
    public function truncate(): void
    {
        $model = $this->getModel();
        $model->getConnection()->truncate($model->getTable());
    }
}

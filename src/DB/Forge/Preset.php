<?php
declare(strict_types=1);

namespace Fyre\DB\Forge;

use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Connection;
use Fyre\DB\Exceptions\DbException;
use Fyre\DB\Schema\SchemaRegistry;

/**
 * Provides a preset database table definition.
 */
abstract class Preset
{
    use DebugTrait;

    public const TABLE = '';

    /**
     * Constructs a Preset.
     *
     * @param ForgeRegistry $forgeRegistry The ForgeRegistry.
     * @param SchemaRegistry $schemaRegistry The SchemaRegistry.
     */
    public function __construct(
        protected ForgeRegistry $forgeRegistry,
        protected SchemaRegistry $schemaRegistry
    ) {}

    /**
     * Ensures the preset table exists.
     *
     * @param Connection $connection The Connection.
     */
    public function check(Connection $connection): void
    {
        $forge = $this->forgeRegistry->use($connection);
        $schema = $this->schemaRegistry->use($connection);

        try {
            static::build($forge)->execute();
        } catch (DbException $e) {
            // Another process may have applied the preset after the schema was read.
            $schema->clear();

            if (static::build($forge)->sql() !== []) {
                throw $e;
            }
        }
    }

    /**
     * Checks whether the preset table exists.
     *
     * @param Connection $connection The Connection.
     * @return bool Whether the preset table exists.
     */
    public function exists(Connection $connection): bool
    {
        return $this->schemaRegistry
            ->use($connection)
            ->hasTable(static::TABLE);
    }

    /**
     * Builds the preset table definition.
     *
     * @param Forge $forge The Forge.
     * @return Table The Table.
     */
    abstract protected static function build(Forge $forge): Table;
}

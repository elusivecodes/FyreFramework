<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Migration\MigrationRunner;
use Override;

/**
 * Implements the db rollback console command.
 *
 * Rolls back previously applied migrations.
 */
class DbRollbackCommand extends Command
{
    #[Override]
    protected string|null $alias = 'db:rollback';

    #[Override]
    protected string $description = 'Perform database rollbacks.';

    #[Override]
    protected array $options = [
        'db' => [
            'default' => ConnectionManager::DEFAULT,
        ],
        'batches' => [
            'as' => 'integer',
            'default' => 1,
        ],
        'steps' => [
            'as' => 'integer',
            'default' => null,
        ],
    ];

    /**
     * Runs the command.
     *
     * Note: The connection is resolved using the supplied `$db` key before rolling back migrations.
     *
     * @param ConnectionManager $connectionManager The ConnectionManager.
     * @param MigrationRunner $migrationRunner The MigrationRunner.
     * @param string $db The connection key.
     * @param int|null $batches The number of batches to rollback.
     * @param int|null $steps The number of steps to rollback.
     * @return int|null The exit code.
     */
    public function run(ConnectionManager $connectionManager, MigrationRunner $migrationRunner, string $db, int|null $batches = 1, int|null $steps = null): int|null
    {
        $connection = $connectionManager->use($db);

        $migrationRunner
            ->setConnection($connection)
            ->rollback($batches, $steps);

        return static::CODE_SUCCESS;
    }
}

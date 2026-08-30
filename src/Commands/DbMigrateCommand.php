<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Migration\MigrationRunner;
use Override;

/**
 * Implements the db migrate console command.
 *
 * Runs all pending migrations using the configured migration namespaces.
 */
class DbMigrateCommand extends Command
{
    #[Override]
    protected string|null $alias = 'db:migrate';

    #[Override]
    protected string $description = 'Perform database migrations.';

    #[Override]
    protected array $options = [
        'db' => [
            'default' => ConnectionManager::DEFAULT,
        ],
        'dryRun' => [
            'as' => 'boolean',
            'default' => false,
        ],
    ];

    /**
     * {@inheritDoc}
     *
     * @param Console $io The Console.
     * @param ConnectionManager $connectionManager The ConnectionManager.
     * @param MigrationRunner $migrationRunner The MigrationRunner.
     */
    public function __construct(
        Console $io,
        protected ConnectionManager $connectionManager,
        protected MigrationRunner $migrationRunner,
    ) {
        parent::__construct($io);
    }

    /**
     * Runs the command.
     *
     * Note: The connection is resolved using the supplied `$db` key before running migrations.
     *
     * @param string $db The connection key.
     * @param bool $dryRun Whether to display the migration plan without executing it.
     * @return int|null The exit code.
     */
    public function run(string $db, bool $dryRun = false): int|null
    {
        $connection = $this->connectionManager->use($db);

        $migrationRunner = $this->migrationRunner->setConnection($connection);

        if (!$dryRun) {
            $migrationRunner->migrate();

            return static::CODE_SUCCESS;
        }

        $migrations = $migrationRunner->getPendingMigrations();

        $data = [];
        foreach ($migrations as $migrationName => $_) {
            $data[] = [$migrationName, 'up'];
        }

        $this->io->table($data, ['Migration', 'Action']);

        return static::CODE_SUCCESS;
    }
}

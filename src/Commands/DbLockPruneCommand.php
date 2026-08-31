<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Forge\Presets\LocksPreset;
use Fyre\DB\Lock;
use Override;

/**
 * Implements the db lock prune console command.
 *
 * Removes expired database locks.
 */
class DbLockPruneCommand extends Command
{
    #[Override]
    protected string|null $alias = 'db:lock:prune';

    #[Override]
    protected string $description = 'Remove expired database locks.';

    #[Override]
    protected array $options = [
        'db' => [
            'default' => ConnectionManager::DEFAULT,
        ],
    ];

    /**
     * {@inheritDoc}
     *
     * @param Console $io The Console.
     * @param ConnectionManager $connectionManager The ConnectionManager.
     * @param LocksPreset $locksPreset The LocksPreset.
     */
    public function __construct(
        Console $io,
        protected ConnectionManager $connectionManager,
        protected LocksPreset $locksPreset,
    ) {
        parent::__construct($io);
    }

    /**
     * Runs the command.
     *
     * @param string $db The connection key.
     * @return int|null The exit code.
     */
    public function run(string $db): int|null
    {
        $connection = $this->connectionManager->use($db);

        if (!$this->locksPreset->exists($connection)) {
            return static::CODE_SUCCESS;
        }

        Lock::clearExpired($connection);

        return static::CODE_SUCCESS;
    }
}

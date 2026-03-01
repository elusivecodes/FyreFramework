<?php
declare(strict_types=1);

namespace Fyre\DB\Handlers\Sqlite;

use Fyre\Core\Attributes\SensitivePropertyArray;
use Fyre\DB\Connection;
use Fyre\DB\DbFeature;
use Fyre\DB\Exceptions\DbException;
use Fyre\DB\QueryGenerator;
use Override;
use Pdo\Sqlite;
use PDOException;

use function array_intersect_key;
use function array_replace;
use function chmod;
use function class_exists;
use function file_exists;
use function http_build_query;
use function sprintf;

/**
 * Provides a SQLite {@see Connection} implementation.
 */
class SqliteConnection extends Connection
{
    #[Override]
    protected static array $defaults = [
        'database' => ':memory:',
        'mask' => 0644,
        'cache' => null,
        'mode' => null,
        'persist' => false,
        'flags' => [],
    ];

    #[Override]
    #[SensitivePropertyArray(['database'])]
    protected array $config;

    protected bool $hasSequences;

    /**
     * {@inheritDoc}
     *
     * @throws DbException If PDO extension is not installed, or the connection fails.
     */
    #[Override]
    public function connect(): void
    {
        if ($this->pdo) {
            return;
        }

        if (!class_exists('PDO')) {
            throw new DbException('Sqlite connection requires PDO extension.');
        }

        $chmod = false;
        if ($this->config['database'] !== ':memory:' && $this->config['mode'] !== 'memory') {
            $chmod = !file_exists($this->config['database']);
        }

        $params = array_intersect_key($this->config, ['cache' => true, 'mode' => true]);

        if ($params !== []) {
            $dsn = 'sqlite:file:'.$this->config['database'].'?'.http_build_query($params);
        } else {
            $dsn = 'sqlite:'.$this->config['database'];
        }

        $options = [
            Sqlite::ATTR_ERRMODE => Sqlite::ERRMODE_EXCEPTION,
        ];

        if ($this->config['persist']) {
            $options[Sqlite::ATTR_PERSISTENT] = true;
        }

        $options = array_replace($options, $this->config['flags']);

        try {
            $this->pdo = new Sqlite($dsn, null, null, $options);
        } catch (PDOException $e) {
            throw new DbException(sprintf(
                'Database connection error: %s',
                $e->getMessage()
            ), previous: $e);
        }

        if ($chmod) {
            @chmod($this->config['database'], $this->config['mask']);
        }
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function disableForeignKeys(): static
    {
        $this->rawQuery('PRAGMA foreign_keys = OFF');

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function enableForeignKeys(): static
    {
        $this->rawQuery('PRAGMA foreign_keys = ON');

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function generator(): QueryGenerator
    {
        return $this->generator ??= $this->container->build(SqliteQueryGenerator::class, ['connection' => $this]);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getCharset(): string
    {
        return (string) $this->rawQuery('PRAGMA ENCODING')->fetchColumn();
    }

    /**
     * Checks whether the database contains any sequences.
     *
     * @return bool Whether the database contains any sequences.
     */
    public function hasSequences(): bool
    {
        return $this->hasSequences ??= $this->select('1')
            ->from('sqlite_master')
            ->where([
                'name' => 'sqlite_sequence',
            ])
            ->execute()
            ->count() > 0;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function setCharset(string $charset): static
    {
        $this->rawQuery('PRAGMA ENCODING = '.$this->quote($charset));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function supports(DbFeature $feature): bool
    {
        return match ($feature) {
            DbFeature::UpdateFrom => true,
            default => false,
        };
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function truncate(string $tableName): static
    {
        if ($this->hasSequences()) {
            $this->delete()
                ->from('sqlite_sequence')
                ->where([
                    'name' => $tableName,
                ])
                ->execute();
        }

        $this->delete()
            ->from($tableName)
            ->execute();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected static function resultSetClass(): string
    {
        return SqliteResultSet::class;
    }
}

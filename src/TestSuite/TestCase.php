<?php
declare(strict_types=1);

namespace Fyre\TestSuite;

use Fyre\Core\Engine;
use Fyre\DB\ConnectionManager;
use Fyre\TestSuite\Fixture\FixtureRegistry;
use Override;

use function assert;
use function in_array;

/**
 * Base PHPUnit test case for the framework test suite.
 *
 * Loads configured fixtures before each test and truncates them after each test, with
 * foreign key checks temporarily disabled while fixtures are applied.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Engine $app;

    /**
     * @var string[]
     */
    protected array $fixtures = [];

    /**
     * Skip the test if the condition is true.
     *
     * @param bool $shouldSkip Whether the test should be skipped.
     * @param string $message The message to display if skipped.
     * @return bool Whether the test was skipped.
     */
    public function skipIf(bool $shouldSkip, string $message = ''): bool
    {
        if ($shouldSkip) {
            $this->markTestSkipped($message);
        }

        return $shouldSkip;
    }

    /**
     * Skip the test unless the condition is true.
     *
     * @param bool $shouldNotSkip Whether the test should not be skipped.
     * @param string $message The message to display if skipped.
     * @return bool Whether the test was not skipped.
     */
    public function skipUnless(bool $shouldNotSkip, string $message = ''): bool
    {
        if (!$shouldNotSkip) {
            $this->markTestSkipped($message);
        }

        return $shouldNotSkip;
    }

    /**
     * Set up the fixtures.
     */
    protected function setupFixtures(): void
    {
        $connection = $this->app->use(ConnectionManager::class)->use();
        $fixtureRegistry = $this->app->use(FixtureRegistry::class);

        $connection->disableForeignKeys();

        try {
            foreach ($this->fixtures as $fixture) {
                $fixtureRegistry->use($fixture)->run();
            }
        } finally {
            $connection->enableForeignKeys();
        }
    }

    /**
     * Tear down the fixtures.
     */
    protected function teardownFixtures(): void
    {
        $fixtureRegistry = $this->app->use(FixtureRegistry::class);
        $connection = $this->app->use(ConnectionManager::class)->use();
        $tables = [];

        foreach ($this->fixtures as $fixtureAlias) {
            foreach ($fixtureRegistry->use($fixtureAlias)->getTables() as $table) {
                if (in_array($table, $tables)) {
                    continue;
                }

                $tables[] = $table;
            }
        }

        $connection->disableForeignKeys();

        try {
            foreach ($tables as $table) {
                $connection->truncate($table);
            }
        } finally {
            $connection->enableForeignKeys();
        }
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function setUp(): void
    {
        $app = Engine::getInstance();

        assert($app instanceof Engine);

        $this->app = $app;
        $this->app->clearScoped();

        $this->setupFixtures();
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function tearDown(): void
    {
        $this->teardownFixtures();
    }
}

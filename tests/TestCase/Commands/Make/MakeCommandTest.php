<?php
declare(strict_types=1);

namespace Tests\TestCase\Commands\Make;

use Fyre\Auth\PolicyRegistry;
use Fyre\Console\CommandRunner;
use Fyre\Console\Console;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Lang;
use Fyre\Core\Loader;
use Fyre\Core\Make;
use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Migration\MigrationRunner;
use Fyre\ORM\EntityLocator;
use Fyre\ORM\ModelRegistry;
use Fyre\TestSuite\Fixture\FixtureRegistry;
use Fyre\Utility\Inflector;
use Fyre\Utility\Path;
use Fyre\View\CellRegistry;
use Fyre\View\HelperRegistry;
use Fyre\View\TemplateLocator;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;
use function fclose;
use function fopen;
use function glob;
use function mkdir;
use function rmdir;
use function unlink;

use const ROOT;

final class MakeCommandTest extends TestCase
{
    use MakeCellTemplateTestTrait;
    use MakeCellTestTrait;
    use MakeCommandCommandTestTrait;
    use MakeConfigTestTrait;
    use MakeControllerTestTrait;
    use MakeElementTestTrait;
    use MakeEntityTestTrait;
    use MakeFixtureTestTrait;
    use MakeFormTestTrait;
    use MakeHelperTestTrait;
    use MakeJobTestTrait;
    use MakeLangTestTrait;
    use MakeLayoutTestTrait;
    use MakeMiddlewareTestTrait;
    use MakeMigrationTestTrait;
    use MakeModelTestTrait;
    use MakePolicyTestTrait;
    use MakeTemplateTestTrait;

    protected CommandRunner $commandRunner;

    /**
     * @var resource
     */
    protected $error;

    /**
     * @var resource
     */
    protected $input;

    /**
     * @var resource
     */
    protected $output;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Make::class)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Loader::class);
        $container->singleton(Inflector::class);
        $container->singleton(Config::class);
        $container->singleton(Lang::class);
        $container->singleton(TemplateLocator::class);
        $container->singleton(CommandRunner::class);
        $container->singleton(CellRegistry::class);
        $container->singleton(EntityLocator::class);
        $container->singleton(FixtureRegistry::class);
        $container->singleton(HelperRegistry::class);
        $container->singleton(MigrationRunner::class);
        $container->singleton(ModelRegistry::class);
        $container->singleton(PolicyRegistry::class);

        $tmpDir = Path::normalize(Path::join(ROOT, 'tmp'));

        $container->use(Loader::class)->addNamespaces([
            'Example\\' => Path::join($tmpDir),
            'Fyre\Commands\\' => Path::normalize(Path::join(ROOT, 'src/Commands')),
        ]);

        $container->use(Config::class)
            ->addPath(Path::join($tmpDir, 'config'))
            ->set('App.defaultLocale', 'en');
        $container->use(Lang::class)->addPath(Path::join($tmpDir, 'lang'));
        $container->use(TemplateLocator::class)->addPath(Path::join($tmpDir, 'templates'));

        $container->use(CellRegistry::class)->addNamespace('Example\Cells');
        $container->use(EntityLocator::class)->addNamespace('Example\Entities');
        $container->use(FixtureRegistry::class)->addNamespace('Example\Fixtures');
        $container->use(HelperRegistry::class)->addNamespace('Example\Helpers');
        $container->use(MigrationRunner::class)->addNamespace('Example\Migrations');
        $container->use(ModelRegistry::class)->addNamespace('Example\Models');
        $container->use(PolicyRegistry::class)->addNamespace('Example\Policies');

        $input = fopen('php://memory', 'r+b');
        $output = fopen('php://memory', 'r+b');
        $error = fopen('php://memory', 'r+b');

        $this->assertIsResource($input);
        $this->assertIsResource($output);
        $this->assertIsResource($error);

        $this->input = $input;
        $this->output = $output;
        $this->error = $error;

        $container->instance(
            Console::class,
            new Console($this->input, $this->output, $this->error)
        );

        $this->commandRunner = $container->use(CommandRunner::class);
        $this->commandRunner
            ->addNamespace('Example\Commands')
            ->addNamespace('Fyre\Commands');

        @mkdir('tmp');
    }

    #[Override]
    protected function tearDown(): void
    {
        foreach (glob('tmp/Migrations/Migration_*_CreateTables.php') ?: [] as $filePath) {
            @unlink($filePath);
        }

        @unlink('tmp/invalid');
        @unlink('tmp/Cells/ExampleCell.php');
        @unlink('tmp/Commands/ExampleCommand.php');
        @unlink('tmp/config/example.php');
        @unlink('tmp/Controllers/ExampleController.php');
        @unlink('tmp/Entities/Example.php');
        @unlink('tmp/Fixtures/ExampleFixture.php');
        @unlink('tmp/Forms/ExampleForm.php');
        @unlink('tmp/Middleware/ExampleMiddleware.php');
        @unlink('tmp/Jobs/ExampleJob.php');
        @unlink('tmp/lang/en/Example.php');
        @unlink('tmp/Helpers/ExampleHelper.php');
        @unlink('tmp/Models/ExampleModel.php');
        @unlink('tmp/Policies/ExamplePolicy.php');
        @unlink('tmp/templates/cells/Example/display.php');
        @unlink('tmp/templates/elements/example.php');
        @unlink('tmp/templates/Example/index.php');
        @unlink('tmp/templates/layouts/default.php');

        @rmdir('tmp/Cells');
        @rmdir('tmp/Commands');
        @rmdir('tmp/config');
        @rmdir('tmp/Controllers');
        @rmdir('tmp/Entities');
        @rmdir('tmp/Fixtures');
        @rmdir('tmp/Forms');
        @rmdir('tmp/Middleware');
        @rmdir('tmp/Jobs');
        @rmdir('tmp/lang/en');
        @rmdir('tmp/lang');
        @rmdir('tmp/Helpers');
        @rmdir('tmp/Migrations');
        @rmdir('tmp/Models');
        @rmdir('tmp/Policies');
        @rmdir('tmp/templates/cells/Example/');
        @rmdir('tmp/templates/cells');
        @rmdir('tmp/templates/elements');
        @rmdir('tmp/templates/Example');
        @rmdir('tmp/templates/layouts');
        @rmdir('tmp/templates');
        @rmdir('tmp');

        fclose($this->input);
        fclose($this->output);
        fclose($this->error);
    }
}

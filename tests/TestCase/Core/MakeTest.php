<?php
declare(strict_types=1);

namespace Tests\TestCase\Core;

use Fyre\Auth\PolicyRegistry;
use Fyre\Console\Command;
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
use function file_put_contents;
use function fopen;
use function mkdir;
use function rmdir;
use function unlink;

use const ROOT;

final class MakeTest extends TestCase
{
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

    public function testMakeCell(): void
    {
        $this->commandRunner->run('make:cell', ['Example']);

        $filePath = 'tmp/Cells/ExampleCell.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('cell', [
                '{namespace}' => 'Example\Cells',
                '{class}' => 'ExampleCell',
                '{method}' => 'display',
            ]),
            $filePath
        );
    }

    public function testMakeCellTemplate(): void
    {
        $this->commandRunner->run('make:cell_template', ['Example.display']);

        $filePath = 'tmp/templates/cells/Example/display.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('cell_template'),
            $filePath
        );
    }

    public function testMakeCommand(): void
    {
        $this->commandRunner->run('make:command', ['Example']);

        $filePath = 'tmp/Commands/ExampleCommand.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('command', [
                '{namespace}' => 'Example\Commands',
                '{class}' => 'ExampleCommand',
                '{alias}' => 'example',
                '{name}' => 'Example',
                '{description}' => '',
            ]),
            $filePath
        );
    }

    public function testMakeCommandsIncludeForceOption(): void
    {
        $commands = $this->commandRunner->all();

        foreach ([
            'make:cell',
            'make:cell_template',
            'make:command',
            'make:config',
            'make:controller',
            'make:element',
            'make:entity',
            'make:fixture',
            'make:form',
            'make:helper',
            'make:job',
            'make:lang',
            'make:layout',
            'make:middleware',
            'make:migration',
            'make:model',
            'make:policy',
            'make:template',
        ] as $alias) {
            $this->assertSame(
                [
                    'as' => 'boolean',
                    'default' => false,
                ],
                $commands[$alias]['options']['force']
            );
        }
    }

    public function testMakeConfig(): void
    {
        $this->commandRunner->run('make:config', ['example']);

        $filePath = 'tmp/config/example.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('config'),
            $filePath
        );
    }

    public function testMakeController(): void
    {
        $this->commandRunner->run('make:controller', ['Example', 'namespace' => 'Example\Controllers']);

        $filePath = 'tmp/Controllers/ExampleController.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('controller', [
                '{namespace}' => 'Example\Controllers',
                '{class}' => 'ExampleController',
            ]),
            $filePath
        );
    }

    public function testMakeElement(): void
    {
        $this->commandRunner->run('make:element', ['example']);

        $filePath = 'tmp/templates/elements/example.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('element'),
            $filePath
        );
    }

    public function testMakeEntity(): void
    {
        $this->commandRunner->run('make:entity', ['Example']);

        $filePath = 'tmp/Entities/Example.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('entity', [
                '{namespace}' => 'Example\Entities',
                '{class}' => 'Example',
            ]),
            $filePath
        );
    }

    public function testMakeFixture(): void
    {
        $this->commandRunner->run('make:fixture', ['Example']);

        $filePath = 'tmp/Fixtures/ExampleFixture.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('fixture', [
                '{namespace}' => 'Example\Fixtures',
                '{class}' => 'ExampleFixture',
            ]),
            $filePath
        );
    }

    public function testMakeForm(): void
    {
        $this->commandRunner->run('make:form', ['Example', 'namespace' => 'Example\Forms']);

        $filePath = 'tmp/Forms/ExampleForm.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('form', [
                '{namespace}' => 'Example\Forms',
                '{class}' => 'ExampleForm',
            ]),
            $filePath
        );
    }

    public function testMakeHelper(): void
    {
        $this->commandRunner->run('make:helper', ['Example']);

        $filePath = 'tmp/Helpers/ExampleHelper.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('helper', [
                '{namespace}' => 'Example\Helpers',
                '{class}' => 'ExampleHelper',
            ]),
            $filePath
        );
    }

    public function testMakeJob(): void
    {
        $this->commandRunner->run('make:job', ['Example', 'namespace' => 'Example\Jobs']);

        $filePath = 'tmp/Jobs/ExampleJob.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('job', [
                '{namespace}' => 'Example\Jobs',
                '{class}' => 'ExampleJob',
            ]),
            $filePath
        );
    }

    public function testMakeLang(): void
    {
        $this->commandRunner->run('make:lang', ['Example']);

        $filePath = 'tmp/lang/en/Example.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('lang'),
            $filePath
        );
    }

    public function testMakeLayout(): void
    {
        $this->commandRunner->run('make:layout', ['default']);

        $filePath = 'tmp/templates/layouts/default.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('layout'),
            $filePath
        );
    }

    public function testMakeMiddleware(): void
    {
        $this->commandRunner->run('make:middleware', ['Example', 'namespace' => 'Example\Middleware']);

        $filePath = 'tmp/Middleware/ExampleMiddleware.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('middleware', [
                '{namespace}' => 'Example\Middleware',
                '{class}' => 'ExampleMiddleware',
            ]),
            $filePath
        );
    }

    public function testMakeMigration(): void
    {
        $this->commandRunner->run('make:migration', ['CreateTables', '20240101']);

        $filePath = 'tmp/Migrations/Migration_20240101_CreateTables.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('migration', [
                '{namespace}' => 'Example\Migrations',
                '{class}' => 'Migration_20240101_CreateTables',
            ]),
            $filePath
        );
    }

    public function testMakeModel(): void
    {
        $this->commandRunner->run('make:model', ['Example']);

        $filePath = 'tmp/Models/ExampleModel.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('model', [
                '{namespace}' => 'Example\Models',
                '{class}' => 'ExampleModel',
            ]),
            $filePath
        );
    }

    public function testMakeModelForce(): void
    {
        $filePath = 'tmp/Models/ExampleModel.php';

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:model', ['Example'])
        );

        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:model', ['Example'])
        );

        $this->assertStringEqualsFile($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:model', [
                'Example',
                'force' => true,
            ])
        );

        $this->assertFileMatchesFormat(
            Make::loadStub('model', [
                '{namespace}' => 'Example\Models',
                '{class}' => 'ExampleModel',
            ]),
            $filePath
        );
    }

    public function testMakePolicy(): void
    {
        $this->commandRunner->run('make:policy', ['Example']);

        $filePath = 'tmp/Policies/ExamplePolicy.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('policy', [
                '{namespace}' => 'Example\Policies',
                '{class}' => 'ExamplePolicy',
            ]),
            $filePath
        );
    }

    public function testMakeTemplate(): void
    {
        $this->commandRunner->run('make:template', ['Example.index']);

        $filePath = 'tmp/templates/Example/index.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('template'),
            $filePath
        );
    }

    public function testMakeTemplateForce(): void
    {
        $filePath = 'tmp/templates/Example/index.php';

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:template', ['Example.index'])
        );

        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:template', ['Example.index'])
        );

        $this->assertStringEqualsFile($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:template', [
                'Example.index',
                'force' => true,
            ])
        );

        $this->assertFileMatchesFormat(
            Make::loadStub('template'),
            $filePath
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

        $this->input = fopen('php://memory', 'r+b');
        $this->output = fopen('php://memory', 'r+b');
        $this->error = fopen('php://memory', 'r+b');

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
        @unlink('tmp/Migrations/Migration_20240101_CreateTables.php');
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

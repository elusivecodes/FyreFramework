<?php
declare(strict_types=1);

namespace Tests\TestCase\Commands\Make;

use Fyre\Console\Command;
use Fyre\Console\CommandRunner;
use Fyre\Console\Console;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Loader;
use Fyre\Core\Make;
use Fyre\DB\TypeParser;
use Fyre\Event\EventManager;
use Fyre\Utility\Inflector;
use Fyre\Utility\Path;
use Fyre\View\TemplateLocator;
use Override;
use PHPUnit\Framework\TestCase;

use function fclose;
use function file_put_contents;
use function fopen;
use function mkdir;
use function rewind;
use function rmdir;
use function stream_get_contents;
use function unlink;

use const ROOT;

final class MakeCellTemplateTest extends TestCase
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

    public function testMakeCellTemplate(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:cell_template', ['Example.display'])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;32mGenerated: tmp/templates/cells/Example/display.php\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            '',
            stream_get_contents($this->error)
        );

        $filePath = 'tmp/templates/cells/Example/display.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('cell_template'),
            $filePath
        );
    }

    public function testMakeCellTemplateExistingFile(): void
    {
        $filePath = 'tmp/templates/cells/Example/display.php';
        @mkdir('tmp/templates/cells/Example', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:cell_template', ['Example.display'])
        );

        rewind($this->output);

        $this->assertSame(
            '',
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            "\033[0;31mCell template file already exists.\033[0m".PHP_EOL,
            stream_get_contents($this->error)
        );

        $this->assertStringEqualsFile($filePath, 'changed');
    }

    public function testMakeCellTemplateForce(): void
    {
        $filePath = 'tmp/templates/cells/Example/display.php';
        @mkdir('tmp/templates/cells/Example', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:cell_template', [
                'Example.display',
                'force' => true,
            ])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;32mGenerated: ".$filePath."\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            '',
            stream_get_contents($this->error)
        );

        $this->assertFileMatchesFormat(
            Make::loadStub('cell_template'),
            $filePath
        );
    }

    public function testMakeCellTemplateInvalidPath(): void
    {
        file_put_contents('tmp/invalid', 'invalid');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:cell_template', [
                'Example.display',
                'path' => 'tmp/invalid',
            ])
        );

        rewind($this->output);

        $this->assertSame(
            '',
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            "\033[0;31mInvalid template path.\033[0m".PHP_EOL,
            stream_get_contents($this->error)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Loader::class);
        $container->singleton(Inflector::class);
        $container->singleton(Config::class);
        $container->singleton(EventManager::class);
        $container->singleton(TypeParser::class);
        $container->singleton(CommandRunner::class);
        $container->singleton(Make::class);
        $container->singleton(TemplateLocator::class);

        $tmpDir = Path::join(ROOT, 'tmp');

        $container->use(Loader::class)->addNamespaces([
            'Fyre\Commands\\' => Path::join(ROOT, 'src/Commands'),
        ]);
        $container->use(TemplateLocator::class)->addPath(Path::join($tmpDir, 'templates'));

        $input = fopen('php://memory', 'r+b');
        $output = fopen('php://memory', 'r+b');
        $error = fopen('php://memory', 'r+b');

        $this->assertIsResource($input);
        $this->assertIsResource($output);
        $this->assertIsResource($error);

        $this->input = $input;
        $this->output = $output;
        $this->error = $error;

        $container->instance(Console::class, new Console($input, $output, $error));

        $this->commandRunner = $container->use(CommandRunner::class);
        $this->commandRunner->addNamespace('Fyre\Commands');

        @mkdir('tmp');
    }

    #[Override]
    protected function tearDown(): void
    {
        @unlink('tmp/invalid');
        @unlink('tmp/templates/cells/Example/display.php');
        @rmdir('tmp/templates/cells/Example');
        @rmdir('tmp/templates/cells');
        @rmdir('tmp/templates');
        @rmdir('tmp');

        fclose($this->input);
        fclose($this->output);
        fclose($this->error);
    }
}

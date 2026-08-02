<?php
declare(strict_types=1);

namespace Tests\TestCase\View;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Utility\Path;
use Fyre\View\TemplateLocator;
use Override;
use PHPUnit\Framework\TestCase;

use function bin2hex;
use function class_uses;
use function file_put_contents;
use function is_link;
use function mkdir;
use function random_bytes;
use function realpath;
use function rmdir;
use function symlink;
use function sys_get_temp_dir;
use function unlink;

final class TemplateLocatorTest extends TestCase
{
    protected TemplateLocator $templateLocator;

    public function testAddPath(): void
    {
        $this->templateLocator->addPath('tests/templates1');
        $this->templateLocator->addPath('tests/templates2');

        $this->assertSame(
            [
                Path::resolve('tests/templates1'),
                Path::resolve('tests/templates2'),
            ],
            $this->templateLocator->getPaths()
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(TemplateLocator::class)
        );
    }

    public function testLocate(): void
    {
        $this->templateLocator->addPath('tests/templates');

        $this->assertSame(
            realpath('tests/templates/test/template.php'),
            $this->templateLocator->locate('template', 'test')
        );
    }

    public function testLocateDeep(): void
    {
        $this->templateLocator->addPath('tests/templates');

        $this->assertSame(
            realpath('tests/templates/test/deep/test.php'),
            $this->templateLocator->locate('deep/test', 'test')
        );
    }

    public function testLocateNullByte(): void
    {
        $this->templateLocator->addPath('tests/templates');

        $this->assertNull(
            $this->templateLocator->locate("test\0/../../src/functions")
        );
    }

    public function testLocateParentTraversal(): void
    {
        $this->templateLocator->addPath('tests/templates');

        $this->assertNull(
            $this->templateLocator->locate('../../src/functions')
        );
    }

    public function testLocateSymlinkOutsidePath(): void
    {
        $suffix = random_bytes(8) |> bin2hex(...);
        $rootPath = sys_get_temp_dir().'/fyre-template-root-'.$suffix;
        $outsidePath = sys_get_temp_dir().'/fyre-template-outside-'.$suffix.'.php';
        $linkPath = $rootPath.'/outside.php';

        mkdir($rootPath);
        file_put_contents($outsidePath, '<?php');

        if (!@symlink($outsidePath, $linkPath)) {
            unlink($outsidePath);
            rmdir($rootPath);
            $this->markTestSkipped('Symbolic links are not available.');
        }

        try {
            $this->templateLocator->addPath($rootPath);

            $this->assertNull(
                $this->templateLocator->locate('outside')
            );
        } finally {
            if (is_link($linkPath)) {
                unlink($linkPath);
            }

            unlink($outsidePath);
            rmdir($rootPath);
        }
    }

    public function testRemovePath(): void
    {
        $this->templateLocator->addPath('tests/templates');

        $this->assertSame(
            $this->templateLocator,
            $this->templateLocator->removePath('tests/templates')
        );

        $this->assertEmpty(
            $this->templateLocator->getPaths()
        );
    }

    public function testRemovePathInvalid(): void
    {
        $this->assertSame(
            $this->templateLocator,
            $this->templateLocator->removePath('tests/Mock/invalid')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->templateLocator = new TemplateLocator();
    }
}

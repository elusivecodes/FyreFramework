<?php
declare(strict_types=1);

namespace Tests\TestCase\View;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Utility\Path;
use Fyre\View\TemplateLocator;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

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
            Path::resolve('tests/templates/test/template.php'),
            $this->templateLocator->locate('template', 'test')
        );
    }

    public function testLocateDeep(): void
    {
        $this->templateLocator->addPath('tests/templates');

        $this->assertSame(
            Path::resolve('tests/templates/test/deep/test.php'),
            $this->templateLocator->locate('deep/test', 'test')
        );
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

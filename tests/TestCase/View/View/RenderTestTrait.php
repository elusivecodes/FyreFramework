<?php
declare(strict_types=1);

namespace Tests\TestCase\View\View;

use Fyre\Event\Event;
use InvalidArgumentException;
use RuntimeException;

use function ob_get_level;
use function realpath;

trait RenderTestTrait
{
    public function testEventAfterRender(): void
    {
        $ran = false;
        $this->view->getEventManager()->on('View.afterRender', function(Event $event, string $filePath, string $content) use (&$ran): void {
            $ran = true;

            $this->assertSame(
                realpath('tests/templates/test/template.php'),
                $filePath
            );

            $this->assertSame('Template: 1', $content);
        });

        $this->view->setData([
            'a' => 1,
        ]);

        $this->view->setLayout(null);

        $this->view->render('test/template');

        $this->assertTrue($ran);
    }

    public function testEventBeforeRender(): void
    {
        $ran = false;
        $this->view->getEventManager()->on('View.beforeRender', function(Event $event, string $filePath) use (&$ran): void {
            $ran = true;

            $this->assertSame(
                realpath('tests/templates/test/template.php'),
                $filePath
            );
        });

        $this->view->setData([
            'a' => 1,
        ]);

        $this->view->setLayout(null);

        $this->view->render('test/template');

        $this->assertTrue($ran);
    }

    public function testRenderData(): void
    {
        $this->view->setData([
            'a' => 1,
        ]);

        $this->view->setLayout(null);

        $this->assertSame(
            'Template: 1',
            $this->view->render('test/template')
        );
    }

    public function testRenderDataCannotOverrideFilePath(): void
    {
        $this->view->setData([
            'a' => 1,
            '__fyreFilePath' => realpath('tests/templates/test/deep/test.php'),
        ]);
        $this->view->setLayout(null);

        $this->assertSame(
            'Template: 1',
            $this->view->render('test/template')
        );
    }

    public function testRenderDataCannotOverrideView(): void
    {
        $this->view->setData([
            'a' => 1,
            'this' => null,
        ]);

        $this->assertSame(
            'Content: Template: 1',
            $this->view->render('test/template')
        );
    }

    public function testRenderDeep(): void
    {
        $this->view->setLayout(null);

        $this->assertSame(
            'Test',
            $this->view->render('test/deep/test')
        );
    }

    public function testRenderExceptionCleanup(): void
    {
        $bufferLevel = ob_get_level();
        $this->view->setLayout(null);

        try {
            $this->view->render('test/exception');
            $this->fail('Expected render to throw an exception.');
        } catch (RuntimeException $e) {
            $this->assertSame('Test exception.', $e->getMessage());
        }

        $this->assertSame($bufferLevel, ob_get_level());

        $this->view->set('a', 1);

        $this->assertSame(
            'Template: 1',
            $this->view->render('test/template')
        );
    }

    public function testRenderInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Template `invalid` could not be found.');

        $this->view->render('invalid');
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\View\View;

use Fyre\Event\Event;
use InvalidArgumentException;

use function realpath;

trait ElementTestTrait
{
    public function testElementData(): void
    {
        $this->view->setLayout(null);

        $this->assertSame(
            'Element: 2',
            $this->view->render('test/element')
        );
    }

    public function testElementDataCannotOverrideFilePath(): void
    {
        $this->assertSame(
            'Element: 2',
            $this->view->element('element', [
                'b' => 2,
                '__fyreFilePath' => realpath('tests/templates/test/deep/test.php'),
            ])
        );
    }

    public function testElementDeep(): void
    {
        $this->view->setLayout(null);

        $this->assertSame(
            'Test',
            $this->view->render('test/element_deep')
        );
    }

    public function testElementInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Element template `invalid` could not be found.');

        $this->view->render('test/element_invalid');
    }

    public function testEventAfterElement(): void
    {
        $ran = false;
        $this->view->getEventManager()->on('View.afterElement', function(Event $event, string $filePath, string $content) use (&$ran): void {
            $ran = true;

            $this->assertSame(
                realpath('tests/templates/elements/element.php'),
                $filePath
            );

            $this->assertSame('Element: 2', $content);
        });

        $this->view->setLayout(null);

        $this->view->render('test/element');

        $this->assertTrue($ran);
    }

    public function testEventBeforeElement(): void
    {
        $ran = false;
        $this->view->getEventManager()->on('View.beforeElement', function(Event $event, string $filePath) use (&$ran): void {
            $ran = true;

            $this->assertSame(
                realpath('tests/templates/elements/element.php'),
                $filePath
            );
        });

        $this->view->setLayout(null);

        $this->view->render('test/element');

        $this->assertTrue($ran);
    }
}

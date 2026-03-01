<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form;

use BadMethodCallException;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\ServerRequest;
use Fyre\Security\CsrfProtection;
use Fyre\Utility\FormBuilder;
use Fyre\Utility\HtmlHelper;
use Fyre\View\CellRegistry;
use Fyre\View\Form\Context;
use Fyre\View\HelperRegistry;
use Fyre\View\TemplateLocator;
use Fyre\View\View;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use stdClass;

use function class_uses;

final class FormTest extends TestCase
{
    use ButtonTestTrait;
    use CheckboxTestTrait;
    use CloseTestTrait;
    use DateTestTrait;
    use DateTimeTestTrait;
    use FieldsetCloseTestTrait;
    use FieldsetOpenTestTrait;
    use InputTestTrait;
    use InputTypeTestTrait;
    use LabelTestTrait;
    use LegendTestTrait;
    use NumberTestTrait;
    use OpenMultipartTestTrait;
    use OpenTestTrait;
    use RadioTestTrait;
    use SelectMultiTestTrait;
    use SelectTestTrait;
    use TextareaTestTrait;
    use TimeTestTrait;

    protected Container $container;

    protected View $view;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Context::class)
        );
    }

    public function testInvalidContext(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Item class `stdClass` does not have a mapped context.');

        $this->view->Form->open(new stdClass());
    }

    public function testInvalidInputType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Input type `invalid` is not valid.');

        $this->view->Form->input('input', [
            'type' => 'invalid',
        ]);
    }

    public function testUnclosedForm(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Unable to open form while existing form context is not closed.');

        $this->view->Form->open();
        $this->view->Form->open();
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(Config::class);
        $this->container->singleton(TemplateLocator::class);
        $this->container->singleton(HelperRegistry::class);
        $this->container->singleton(CellRegistry::class);
        $this->container->singleton(HtmlHelper::class);
        $this->container->singleton(FormBuilder::class);
        $this->container->singleton(CsrfProtection::class);

        $this->container->use(Config::class)->set('Csrf.salt', 'l2wyQow3eTwQeTWcfZnlgU8FnbiWljpGjQvNP2pL');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test',
                ],
            ],
        ]);

        $this->view = $this->container->build(View::class, ['request' => $request]);
    }

    #[Override]
    protected function tearDown(): void
    {
        $_POST = [];
    }
}

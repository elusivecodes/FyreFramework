<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Form;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\DB\TypeParser;
use Fyre\Form\Form;
use Fyre\Form\Schema;
use Fyre\Form\Validator;
use Fyre\Http\ServerRequest;
use Fyre\Utility\FormBuilder;
use Fyre\Utility\HtmlHelper;
use Fyre\View\CellRegistry;
use Fyre\View\HelperRegistry;
use Fyre\View\TemplateLocator;
use Fyre\View\View;
use Override;
use PHPUnit\Framework\TestCase;

final class FormContextTest extends TestCase
{
    use BinaryTestTrait;
    use BooleanTestTrait;
    use DateTestTrait;
    use DateTimeTestTrait;
    use DecimalTestTrait;
    use EnumTestTrait;
    use FloatTestTrait;
    use IntegerTestTrait;
    use StringTestTrait;
    use TextTestTrait;
    use TimeTestTrait;

    protected Form $form;

    protected Schema $schema;

    protected Validator $validator;

    protected View $view;

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Config::class);
        $container->singleton(TemplateLocator::class);
        $container->singleton(HelperRegistry::class);
        $container->singleton(CellRegistry::class);
        $container->singleton(HtmlHelper::class);
        $container->singleton(FormBuilder::class);
        $container->singleton(TypeParser::class);

        $this->form = $container->build(Form::class);
        $this->schema = $this->form->getSchema();
        $this->validator = $this->form->getValidator();

        $request = $container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test',
                ],
            ],
        ]);

        $this->view = $container->build(View::class, ['request' => $request]);
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\Form;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Lang;
use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\TypeParser;
use Fyre\Form\Form;
use Fyre\Form\Schema;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\Path;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Enums\State;
use Tests\Mock\Forms\TestForm;

use function class_uses;

use const ROOT;

final class FormTest extends TestCase
{
    protected Container $container;

    protected Form $form;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Form::class)
        );
    }

    public function testExecute(): void
    {
        $this->assertTrue(
            $this->form->execute([
                'title' => 'This is a test',
                'user_id' => '1',
                'value' => '1.1',
                'start' => '2026-01-01',
                'bool' => '1',
            ])
        );

        $data = $this->form->getData();

        $this->assertInstanceOf(DateTime::class, $data['start']);

        $data['start'] = $data['start']->toISOString();

        $this->assertSame(
            [
                'title' => 'This is a test',
                'user_id' => 1,
                'value' => '1.1',
                'start' => '2026-01-01T00:00:00.000+00:00',
                'bool' => true,
            ],
            $data
        );

        $this->assertSame(
            [],
            $this->form->getErrors()
        );
    }

    public function testExecuteFail(): void
    {
        $this->assertFalse(
            $this->form->execute([])
        );

        $this->assertSame(
            [],
            $this->form->getData()
        );

        $this->assertSame(
            [
                'title' => [
                    'The title is required.',
                ],
                'user_id' => [
                    'The user_id is required.',
                ],
                'start' => [
                    'The start is required.',
                ],
            ],
            $this->form->getErrors()
        );
    }

    public function testExecuteNoValidation(): void
    {
        $this->assertTrue(
            $this->form->execute([], false)
        );
    }

    public function testExecuteParsesInvalidEnumToNull(): void
    {
        $form = new class ($this->container) extends Form
        {
            public function buildSchema(Schema $schema): Schema
            {
                return $schema
                    ->addField('status', ['type' => 'string'])
                    ->setEnumClass('status', State::class);
            }
        };

        $this->assertTrue(
            $form->execute([
                'status' => 'Invalid',
            ], false)
        );

        $this->assertNull(
            $form->get('status')
        );
    }

    public function testExecuteParsesUnitEnum(): void
    {
        $form = new class ($this->container) extends Form
        {
            public function buildSchema(Schema $schema): Schema
            {
                return $schema
                    ->addField('status', ['type' => 'string'])
                    ->setEnumClass('status', State::class);
            }
        };

        $this->assertTrue(
            $form->execute([
                'status' => 'Draft',
            ], false)
        );

        $this->assertSame(
            State::Draft,
            $form->get('status')
        );
    }

    public function testSet(): void
    {
        $this->assertSame(
            $this->form,
            $this->form->set('title', 'This is a test')
        );

        $this->assertSame(
            'This is a test',
            $this->form->get('title')
        );
    }

    public function testSetData(): void
    {
        $this->assertSame(
            $this->form,
            $this->form->setData([
                'title' => 'This is a test',
                'user_id' => '1',
                'value' => '1.1',
                'start' => '2026-01-01',
                'bool' => '1',
            ])
        );

        $this->assertSame(
            [
                'title' => 'This is a test',
                'user_id' => '1',
                'value' => '1.1',
                'start' => '2026-01-01',
                'bool' => '1',
            ],
            $this->form->getData()
        );
    }

    public function testValidation(): void
    {
        $this->assertTrue(
            $this->form->validate([
                'title' => 'This is a test',
                'user_id' => '1',
                'value' => '1.1',
                'start' => '2026-01-01',
                'bool' => '1',
            ])
        );

        $this->assertSame([], $this->form->getData());
        $this->assertSame([], $this->form->getErrors());
    }

    public function testValidationFail(): void
    {
        $this->assertFalse(
            $this->form->validate([])
        );

        $this->assertSame([], $this->form->getData());
        $this->assertSame(
            [
                'title' => [
                    'The title is required.',
                ],
                'user_id' => [
                    'The user_id is required.',
                ],
                'start' => [
                    'The start is required.',
                ],
            ],
            $this->form->getErrors()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(TypeParser::class);
        $this->container->singleton(Lang::class);
        $this->container->singleton(Config::class);

        $this->container->use(Config::class)->set('App.locale', 'en');

        $this->container->use(Lang::class)
            ->addPath(Path::join(ROOT, 'lang'));

        $this->form = $this->container->build(TestForm::class);
    }
}

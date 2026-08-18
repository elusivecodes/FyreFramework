<?php
declare(strict_types=1);

namespace Tests\TestCase\Form;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Lang;
use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\TypeParser;
use Fyre\Event\Event;
use Fyre\Form\Form;
use Fyre\Form\Rule;
use Fyre\Form\Schema;
use Fyre\Form\Validator;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\Path;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Enums\State;
use Tests\Mock\Forms\DateValidationForm;
use Tests\Mock\Forms\IntegerValidationForm;
use Tests\Mock\Forms\StateForm;
use Tests\Mock\Forms\TestForm;

use function class_uses;

use const ROOT;

final class FormTest extends TestCase
{
    protected Container $container;

    public function testBuildValidatorEvent(): void
    {
        $form = $this->container->build(TestForm::class);

        $form->getEventManager()->on('Form.buildValidator', static function(Event $event, Validator $validator): void {
            $validator->add('event', Rule::maxLength(4), name: 'maxLength');
        });

        $validator = $form->getValidator();

        $this->assertSame(
            $validator,
            $form->getValidator()
        );

        $this->assertCount(
            1,
            $validator->getFieldRules('event')
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Form::class)
        );
    }

    public function testExecute(): void
    {
        $form = $this->container->build(TestForm::class);

        $this->assertTrue(
            $form->execute([
                'title' => 'This is a test',
                'user_id' => '1',
                'value' => '1.1',
                'start' => '2026-01-01',
                'bool' => '1',
            ])
        );

        $data = $form->getData();

        $this->assertInstanceOf(DateTime::class, $data['start']);

        $data['start'] = $data['start']->toIsoString();

        $this->assertArraysAreIdentical(
            [
                'title' => 'This is a test',
                'user_id' => 1,
                'value' => '1.1',
                'start' => '2026-01-01T00:00:00.000+00:00',
                'bool' => true,
            ],
            $data
        );

        $this->assertArraysAreIdentical(
            [],
            $form->getErrors()
        );
    }

    public function testExecuteFail(): void
    {
        $form = $this->container->build(TestForm::class);

        $this->assertFalse(
            $form->execute([])
        );

        $this->assertArraysAreIdentical(
            [],
            $form->getData()
        );

        $this->assertArraysAreIdentical(
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
            $form->getErrors()
        );
    }

    public function testExecuteNoValidation(): void
    {
        $form = $this->container->build(TestForm::class);

        $this->assertTrue(
            $form->execute([], false)
        );
    }

    public function testExecuteParsesInvalidEnumToNull(): void
    {
        $form = $this->container->build(StateForm::class);

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
        $form = $this->container->build(StateForm::class);

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

    public function testExecuteValidatesBeforeParsing(): void
    {
        $form = $this->container->build(DateValidationForm::class);

        $this->assertTrue(
            $form->execute([
                'start' => '2026-01-01',
            ])
        );

        $this->assertInstanceOf(
            DateTime::class,
            $form->get('start')
        );
    }

    public function testExecuteValidationFailureKeepsRawData(): void
    {
        $form = $this->container->build(IntegerValidationForm::class);

        $this->assertFalse(
            $form->execute([
                'user_id' => '1',
            ])
        );

        $this->assertSame(
            '1',
            $form->get('user_id')
        );
    }

    public function testGetError(): void
    {
        $form = $this->container->build(TestForm::class);
        $form->validate([]);

        $this->assertArraysAreIdentical(
            [
                'The title is required.',
            ],
            $form->getError('title')
        );
    }

    public function testSet(): void
    {
        $form = $this->container->build(TestForm::class);

        $this->assertSame(
            $form,
            $form->set('title', 'This is a test')
        );

        $this->assertSame(
            'This is a test',
            $form->get('title')
        );
    }

    public function testSetData(): void
    {
        $form = $this->container->build(TestForm::class);

        $this->assertSame(
            $form,
            $form->setData([
                'title' => 'This is a test',
                'user_id' => '1',
                'value' => '1.1',
                'start' => '2026-01-01',
                'bool' => '1',
            ])
        );

        $this->assertArraysAreIdentical(
            [
                'title' => 'This is a test',
                'user_id' => '1',
                'value' => '1.1',
                'start' => '2026-01-01',
                'bool' => '1',
            ],
            $form->getData()
        );
    }

    public function testSetSchema(): void
    {
        $form = $this->container->build(TestForm::class);
        $schema = $this->container->build(Schema::class);

        $this->assertSame(
            $form,
            $form->setSchema($schema)
        );

        $this->assertSame(
            $schema,
            $form->getSchema()
        );
    }

    public function testSetValidator(): void
    {
        $form = $this->container->build(TestForm::class);
        $validator = $this->container->build(Validator::class);

        $this->assertSame(
            $form,
            $form->setValidator($validator)
        );

        $this->assertSame(
            $validator,
            $form->getValidator()
        );
    }

    public function testValidation(): void
    {
        $form = $this->container->build(TestForm::class);

        $this->assertTrue(
            $form->validate([
                'title' => 'This is a test',
                'user_id' => '1',
                'value' => '1.1',
                'start' => '2026-01-01',
                'bool' => '1',
            ])
        );

        $this->assertArraysAreIdentical([], $form->getData());
        $this->assertArraysAreIdentical([], $form->getErrors());
    }

    public function testValidationFail(): void
    {
        $form = $this->container->build(TestForm::class);

        $this->assertFalse(
            $form->validate([])
        );

        $this->assertArraysAreIdentical([], $form->getData());
        $this->assertArraysAreIdentical(
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
            $form->getErrors()
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
    }
}

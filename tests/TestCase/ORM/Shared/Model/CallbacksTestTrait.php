<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Mock\Entities\Other;

trait CallbacksTestTrait
{
    /**
     * @return array<string, array{string}>
     */
    public static function deleteCallbackFailureManyProvider(): array
    {
        return [
            'after delete many' => ['failAfterDelete'],
            'before delete many' => ['failBeforeDelete'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function deleteCallbackFailureProvider(): array
    {
        return [
            'after delete' => ['failAfterDelete'],
            'before delete' => ['failBeforeDelete'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function saveCallbackFailureManyProvider(): array
    {
        return [
            'after rules many' => ['failAfterRules'],
            'after save many' => ['failAfterSave'],
            'before rules many' => ['failBeforeRules'],
            'before save many' => ['failBeforeSave'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function saveCallbackFailureProvider(): array
    {
        return [
            'after rules' => ['failAfterRules'],
            'after save' => ['failAfterSave'],
            'before rules' => ['failBeforeRules'],
            'before save' => ['failBeforeSave'],
            'rules' => ['failRules'],
            'validation' => [''],
        ];
    }

    public function testAfterFind(): void
    {
        $Others = $this->modelRegistry->use('Others');

        $other = $Others->newEntity([
            'value' => 1,
        ]);

        $this->assertTrue(
            $Others->save($other)
        );

        $other = $Others->find()->first();

        $this->assertInstanceOf(
            Other::class,
            $other
        );

        $this->assertSame(
            'Test',
            $other->get('test')
        );
    }

    public function testAfterParse(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item = $Items->newEntity([
            'name' => 'afterParse',
        ]);

        $this->assertSame(
            1,
            $item->get('test')
        );
    }

    public function testAfterParseMany(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => 'afterParse',
            ],
            [
                'name' => 'afterParse',
            ],
        ]);

        $this->assertSame(
            1,
            $items[0]->get('test')
        );

        $this->assertSame(
            1,
            $items[1]->get('test')
        );
    }

    public function testBeforeFind(): void
    {
        $Others = $this->modelRegistry->use('Others');

        $others = $Others->newEntities([
            [
                'value' => 1,
            ],
            [
                'value' => 2,
            ],
        ]);

        $this->assertTrue(
            $Others->saveMany($others)
        );

        $this->assertSame(
            1,
            $Others->find()->count()
        );
    }

    public function testBeforeParse(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item = $Items->newEntity([
            'name' => '  Test  ',
        ]);

        $this->assertSame(
            'Test',
            $item->name
        );
    }

    public function testBeforeParseMany(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => '   Test 1   ',
            ],
            [
                'name' => '   Test 2   ',
            ],
        ]);

        $this->assertSame(
            'Test 1',
            $items[0]->name
        );

        $this->assertSame(
            'Test 2',
            $items[1]->name
        );
    }

    public function testBuildValidator(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $validator = $Items->getValidator();

        $this->assertCount(
            1,
            $validator->getFieldRules('event')
        );
    }

    #[DataProvider('deleteCallbackFailureProvider')]
    public function testDeleteCallbackFailure(string $failure): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item = $Items->newEntity([
            'name' => $failure,
        ]);

        $this->assertTrue(
            $Items->save($item)
        );

        $this->assertFalse(
            $Items->delete($item)
        );

        $this->assertSame(
            1,
            $Items->find()->count()
        );
    }

    #[DataProvider('deleteCallbackFailureManyProvider')]
    public function testDeleteCallbackFailureMany(string $failure): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => 'Test',
            ],
            [
                'name' => $failure,
            ],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $this->assertFalse(
            $Items->deleteMany($items)
        );

        $this->assertSame(
            2,
            $Items->find()->count()
        );
    }

    public function testRulesNoCheckRules(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item = $Items->newEntity([
            'name' => 'failRules',
        ]);

        $this->assertTrue(
            $Items->save($item, checkRules: false)
        );

        $this->assertSame(
            1,
            $Items->find()->count()
        );
    }

    #[DataProvider('saveCallbackFailureProvider')]
    public function testSaveCallbackFailure(string $failure): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item = $Items->newEntity([
            'name' => $failure,
        ]);

        $this->assertFalse(
            $Items->save($item)
        );

        $this->assertSame(
            0,
            $Items->find()->count()
        );
    }

    #[DataProvider('saveCallbackFailureManyProvider')]
    public function testSaveCallbackFailureMany(string $failure): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => 'Test',
            ],
            [
                'name' => $failure,
            ],
        ]);

        $this->assertFalse(
            $Items->saveMany($items)
        );

        $this->assertSame(
            0,
            $Items->find()->count()
        );
    }

    public function testValidationNoCheckRules(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item = $Items->newEntity([
            'name' => '',
        ]);

        $this->assertFalse(
            $Items->save($item, checkRules: false)
        );

        $this->assertSame(
            0,
            $Items->find()->count()
        );
    }
}

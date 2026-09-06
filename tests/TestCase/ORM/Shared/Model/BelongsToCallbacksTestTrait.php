<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Mock\Entities\Address;

use function array_map;

trait BelongsToCallbacksTestTrait
{
    /**
     * @return array<string, array{string}>
     */
    public static function belongsToSaveCallbackFailureManyProvider(): array
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
    public static function belongsToSaveCallbackFailureProvider(): array
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

    public function testBelongsToAfterParse(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => 'Test',
            'user' => [
                'name' => 'afterParse',
            ],
        ]);

        $this->assertSame(
            1,
            $address->user->get('test')
        );
    }

    public function testBelongsToAfterParseMany(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $addresses = $Addresses->newEntities([
            [
                'suburb' => 'Test 1',
                'user' => [
                    'name' => 'Test 1',
                ],
            ],
            [
                'suburb' => 'Test 2',
                'user' => [
                    'name' => 'afterParse',
                ],
            ],
        ]);

        $this->assertSame(
            1,
            $addresses[1]->user->get('test')
        );
    }

    public function testBelongsToBeforeParse(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => 'Test',
            'user' => [
                'name' => '  Test  ',
            ],
        ]);

        $this->assertSame(
            'Test',
            $address->user->name
        );
    }

    public function testBelongsToBeforeParseMany(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $addresses = $Addresses->newEntities([
            [
                'suburb' => 'Test 1',
                'user' => [
                    'name' => '  Test 1  ',
                ],
            ],
            [
                'suburb' => 'Test 2',
                'user' => [
                    'name' => '  Test 2  ',
                ],
            ],
        ]);

        $this->assertSame(
            'Test 1',
            $addresses[0]->user->name
        );

        $this->assertSame(
            'Test 2',
            $addresses[1]->user->name
        );
    }

    public function testBelongsToRulesNoCheckRules(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => 'Test',
            'user' => [
                'name' => 'failRules',
            ],
        ]);

        $this->assertTrue(
            $Addresses->save($address, checkRules: false)
        );

        $this->assertSame(
            1,
            $Addresses->find()->count()
        );

        $this->assertSame(
            1,
            $this->modelRegistry->use('Users')->find()->count()
        );
    }

    #[DataProvider('belongsToSaveCallbackFailureProvider')]
    public function testBelongsToSaveCallbackFailure(string $failure): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => 'Test',
            'user' => [
                'name' => $failure,
            ],
        ]);

        $this->assertFalse(
            $Addresses->save($address)
        );

        $this->assertNull(
            $address->id
        );

        $this->assertNull(
            $address->user->id
        );

        $this->assertNull(
            $address->user_id
        );

        $this->assertSame(
            0,
            $Addresses->find()->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Users')->find()->count()
        );
    }

    #[DataProvider('belongsToSaveCallbackFailureManyProvider')]
    public function testBelongsToSaveCallbackFailureMany(string $failure): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $addresses = $Addresses->newEntities([
            [
                'suburb' => 'Test 1',
                'user' => [
                    'name' => 'Test 1',
                ],
            ],
            [
                'suburb' => 'Test 2',
                'user' => [
                    'name' => $failure,
                ],
            ],
        ]);

        $this->assertFalse(
            $Addresses->saveMany($addresses)
        );

        $this->assertArraysAreIdentical(
            [null, null],
            array_map(
                static fn(Address $address): int|null => $address->id,
                $addresses
            )
        );

        $this->assertArraysAreIdentical(
            [null, null],
            array_map(
                static fn(Address $address): int|null => $address->user->id,
                $addresses
            )
        );

        $this->assertArraysAreIdentical(
            [null, null],
            array_map(
                static fn(Address $address): int|null => $address->user_id,
                $addresses
            )
        );

        $this->assertSame(
            0,
            $Addresses->find()->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Users')->find()->count()
        );
    }

    public function testBelongsToValidationNoCheckRules(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => 'Test',
            'user' => [
                'name' => '',
            ],
        ]);

        $this->assertFalse(
            $Addresses->save($address, checkRules: false)
        );

        $this->assertNull(
            $address->id
        );

        $this->assertNull(
            $address->user->id
        );

        $this->assertNull(
            $address->user_id
        );

        $this->assertSame(
            0,
            $Addresses->find()->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Users')->find()->count()
        );
    }
}

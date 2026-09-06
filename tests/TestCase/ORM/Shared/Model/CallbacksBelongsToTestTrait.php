<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Mock\Entities\Address;

use function array_map;

trait CallbacksBelongsToTestTrait
{
    /**
     * @return array<string, array{string}>
     */
    public static function deleteCallbackFailureBelongsToProvider(): array
    {
        return [
            'after delete belongs to' => ['failAfterDelete'],
            'before delete belongs to' => ['failBeforeDelete'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function deleteCallbackFailureManyBelongsToProvider(): array
    {
        return [
            'after delete many belongs to' => ['failAfterDelete'],
            'before delete many belongs to' => ['failBeforeDelete'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function saveCallbackFailureBelongsToProvider(): array
    {
        return [
            'after rules belongs to' => ['failAfterRules'],
            'after save belongs to' => ['failAfterSave'],
            'before rules belongs to' => ['failBeforeRules'],
            'before save belongs to' => ['failBeforeSave'],
            'rules belongs to' => ['failRules'],
            'validation belongs to' => [''],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function saveCallbackFailureManyBelongsToProvider(): array
    {
        return [
            'after rules many belongs to' => ['failAfterRules'],
            'after save many belongs to' => ['failAfterSave'],
            'before rules many belongs to' => ['failBeforeRules'],
            'before save many belongs to' => ['failBeforeSave'],
        ];
    }

    #[DataProvider('deleteCallbackFailureBelongsToProvider')]
    public function testDeleteCallbackFailureBelongsTo(string $failure): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => $failure,
            'user' => [
                'name' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Addresses->save($address)
        );

        $this->assertFalse(
            $Addresses->delete($address)
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

    #[DataProvider('deleteCallbackFailureManyBelongsToProvider')]
    public function testDeleteCallbackFailureManyBelongsTo(string $failure): void
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
                'suburb' => $failure,
                'user' => [
                    'name' => 'Test 2',
                ],
            ],
        ]);

        $this->assertTrue(
            $Addresses->saveMany($addresses)
        );

        $this->assertFalse(
            $Addresses->deleteMany($addresses)
        );

        $this->assertSame(
            2,
            $Addresses->find()->count()
        );

        $this->assertSame(
            2,
            $this->modelRegistry->use('Users')->find()->count()
        );
    }

    public function testRulesNoCheckRulesBelongsTo(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => 'failRules',
            'user' => [
                'name' => 'Test',
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

    #[DataProvider('saveCallbackFailureBelongsToProvider')]
    public function testSaveCallbackFailureBelongsTo(string $failure): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => $failure,
            'user' => [
                'name' => 'Test',
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

    #[DataProvider('saveCallbackFailureManyBelongsToProvider')]
    public function testSaveCallbackFailureManyBelongsTo(string $failure): void
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
                'suburb' => $failure,
                'user' => [
                    'name' => 'Test 2',
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

    public function testValidationNoCheckRulesBelongsTo(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => '',
            'user' => [
                'name' => 'Test',
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

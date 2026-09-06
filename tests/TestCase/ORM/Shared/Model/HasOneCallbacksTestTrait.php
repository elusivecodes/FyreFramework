<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Mock\Entities\User;

use function array_map;

trait HasOneCallbacksTestTrait
{
    /**
     * @return array<string, array{string}>
     */
    public static function hasOneDeleteCallbackFailureManyProvider(): array
    {
        return [
            'after delete many' => ['failAfterDelete'],
            'before delete many' => ['failBeforeDelete'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function hasOneDeleteCallbackFailureProvider(): array
    {
        return [
            'after delete' => ['failAfterDelete'],
            'before delete' => ['failBeforeDelete'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function hasOneSaveCallbackFailureManyProvider(): array
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
    public static function hasOneSaveCallbackFailureProvider(): array
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

    #[DataProvider('hasOneDeleteCallbackFailureProvider')]
    public function testHasOneDeleteCallbackFailure(string $failure): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => $failure,
            'address' => [
                'suburb' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $this->assertFalse(
            $Users->delete($user)
        );

        $this->assertSame(
            1,
            $Users->find()->count()
        );

        $this->assertSame(
            1,
            $this->modelRegistry->use('Addresses')->find()->count()
        );
    }

    #[DataProvider('hasOneDeleteCallbackFailureManyProvider')]
    public function testHasOneDeleteCallbackFailureMany(string $failure): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
                'address' => [
                    'suburb' => 'Test 1',
                ],
            ],
            [
                'name' => $failure,
                'address' => [
                    'suburb' => 'Test 2',
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertFalse(
            $Users->deleteMany($users)
        );

        $this->assertSame(
            2,
            $Users->find()->count()
        );

        $this->assertSame(
            2,
            $this->modelRegistry->use('Addresses')->find()->count()
        );
    }

    public function testHasOneRulesNoCheckRules(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'failRules',
            'address' => [
                'suburb' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Users->save($user, checkRules: false)
        );

        $this->assertSame(
            1,
            $Users->find()->count()
        );

        $this->assertSame(
            1,
            $this->modelRegistry->use('Addresses')->find()->count()
        );
    }

    #[DataProvider('hasOneSaveCallbackFailureProvider')]
    public function testHasOneSaveCallbackFailure(string $failure): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => $failure,
            'address' => [
                'suburb' => 'Test',
            ],
        ]);

        $this->assertFalse(
            $Users->save($user)
        );

        $this->assertNull(
            $user->id
        );

        $this->assertNull(
            $user->address->id
        );

        $this->assertNull(
            $user->address->user_id
        );

        $this->assertSame(
            0,
            $Users->find()->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Addresses')->find()->count()
        );
    }

    #[DataProvider('hasOneSaveCallbackFailureManyProvider')]
    public function testHasOneSaveCallbackFailureMany(string $failure): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
                'address' => [
                    'suburb' => 'Test 1',
                ],
            ],
            [
                'name' => $failure,
                'address' => [
                    'suburb' => 'Test 2',
                ],
            ],
        ]);

        $this->assertFalse(
            $Users->saveMany($users)
        );

        $this->assertArraysAreIdentical(
            [null, null],
            array_map(
                static fn(User $user): int|null => $user->id,
                $users
            )
        );

        $this->assertArraysAreIdentical(
            [null, null],
            array_map(
                static fn(User $user): int|null => $user->address->id,
                $users
            )
        );

        $this->assertArraysAreIdentical(
            [null, null],
            array_map(
                static fn(User $user): int|null => $user->address->user_id,
                $users
            )
        );

        $this->assertSame(
            0,
            $Users->find()->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Addresses')->find()->count()
        );
    }

    public function testHasOneValidationNoCheckRules(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => '',
            'address' => [
                'suburb' => 'Test',
            ],
        ]);

        $this->assertFalse(
            $Users->save($user, checkRules: false)
        );

        $this->assertNull(
            $user->id
        );

        $this->assertNull(
            $user->address->id
        );

        $this->assertNull(
            $user->address->user_id
        );

        $this->assertSame(
            0,
            $Users->find()->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Addresses')->find()->count()
        );
    }
}

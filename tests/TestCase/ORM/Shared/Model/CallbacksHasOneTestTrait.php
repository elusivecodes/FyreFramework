<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Mock\Entities\User;

use function array_map;

trait CallbacksHasOneTestTrait
{
    /**
     * @return array<string, array{string}>
     */
    public static function saveCallbackFailureHasOneProvider(): array
    {
        return [
            'after rules has one' => ['failAfterRules'],
            'after save has one' => ['failAfterSave'],
            'before rules has one' => ['failBeforeRules'],
            'before save has one' => ['failBeforeSave'],
            'rules has one' => ['failRules'],
            'validation has one' => [''],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function saveCallbackFailureManyHasOneProvider(): array
    {
        return [
            'after rules many has one' => ['failAfterRules'],
            'after save many has one' => ['failAfterSave'],
            'before rules many has one' => ['failBeforeRules'],
            'before save many has one' => ['failBeforeSave'],
        ];
    }

    public function testAfterParseHasOne(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'address' => [
                'suburb' => 'afterParse',
            ],
        ]);

        $this->assertSame(
            1,
            $user->address->get('test')
        );
    }

    public function testAfterParseHasOneMany(): void
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
                'name' => 'Test 2',
                'address' => [
                    'suburb' => 'afterParse',
                ],
            ],
        ]);

        $this->assertSame(
            1,
            $users[1]->address->get('test')
        );
    }

    public function testBeforeParseHasOne(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'address' => [
                'suburb' => '  Test  ',
            ],
        ]);

        $this->assertSame(
            'Test',
            $user->address->suburb
        );
    }

    public function testBeforeParseHasOneMany(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
                'address' => [
                    'suburb' => '  Test 1  ',
                ],
            ],
            [
                'name' => 'Test 2',
                'address' => [
                    'suburb' => '  Test 2  ',
                ],
            ],
        ]);

        $this->assertSame(
            'Test 1',
            $users[0]->address->suburb
        );

        $this->assertSame(
            'Test 2',
            $users[1]->address->suburb
        );
    }

    public function testRulesNoCheckRulesHasOne(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'address' => [
                'suburb' => 'failRules',
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

    #[DataProvider('saveCallbackFailureHasOneProvider')]
    public function testSaveCallbackFailureHasOne(string $failure): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'address' => [
                'suburb' => $failure,
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

    #[DataProvider('saveCallbackFailureManyHasOneProvider')]
    public function testSaveCallbackFailureManyHasOne(string $failure): void
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
                'name' => 'Test 2',
                'address' => [
                    'suburb' => $failure,
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

    public function testValidationNoCheckRulesHasOne(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'address' => [
                'suburb' => '',
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

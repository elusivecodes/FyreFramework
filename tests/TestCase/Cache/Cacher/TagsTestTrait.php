<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache\Cacher;

trait TagsTestTrait
{
    public function testTaggedDelete(): void
    {
        $usersCache = $this->cacher->tags('users');

        $usersCache->set('user.1', 'value');

        $this->assertTrue(
            $usersCache->delete('user.1')
        );

        $this->assertNull(
            $usersCache->get('user.1')
        );
    }

    public function testTaggedGetSet(): void
    {
        $usersCache = $this->cacher->tags('users');

        $usersCache->set('user.1', 'value');

        $this->assertSame(
            'value',
            $usersCache->get('user.1')
        );

        $this->assertNull(
            $this->cacher->get('user.1')
        );
    }

    public function testTaggedInvalidateTag(): void
    {
        $usersCache = $this->cacher->tags('users');

        $usersCache->set('user.1', 'value');

        $this->assertTrue(
            $this->cacher->invalidateTag('users')
        );

        $this->assertNull(
            $usersCache->get('user.1')
        );
    }

    public function testTaggedInvalidateTags(): void
    {
        $activeUsersCache = $this->cacher->tags(['users', 'active']);

        $activeUsersCache->set('user.1', 'value');

        $this->assertTrue(
            $this->cacher->invalidateTags(['users', 'active'])
        );

        $this->assertNull(
            $activeUsersCache->get('user.1')
        );
    }

    public function testTaggedRemember(): void
    {
        $usersCache = $this->cacher->tags('users');

        $test = 0;

        $this->assertSame(
            'value',
            $usersCache->remember('user.1', static function() use (&$test): string {
                $test++;

                return 'value';
            })
        );

        $this->assertSame(
            'value',
            $usersCache->remember('user.1', static function() use (&$test): string {
                $test++;

                return 'new';
            })
        );

        $this->assertSame(
            1,
            $test
        );
    }

    public function testTaggedTagsMerges(): void
    {
        $activeUsersCache1 = $this->cacher->tags('users')->tags('active');
        $activeUsersCache2 = $this->cacher->tags(['active', 'users']);

        $activeUsersCache1->set('user.1', 'value');

        $this->assertSame(
            'value',
            $activeUsersCache2->get('user.1')
        );
    }
}

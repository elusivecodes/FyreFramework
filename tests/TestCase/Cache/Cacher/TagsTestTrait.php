<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache\Cacher;

use Fyre\Cache\Cacher;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @property Cacher $cacher
 */
trait TagsTestTrait
{
    /**
     * @return array<string, array{string[], string[]}>
     */
    public static function distinctTagSetsProvider(): array
    {
        return [
            'delimiter in tag' => [['a|b'], ['a', 'b']],
            'different tag boundaries' => [['a|b', 'c'], ['a', 'b|c']],
            'empty tag' => [[], ['']],
        ];
    }

    /**
     * @return array<string, array{string[], string[]}>
     */
    public static function equivalentTagSetsProvider(): array
    {
        return [
            'alphabetical' => [['users', 'active'], ['active', 'users']],
            'leading zero' => [['1', '01'], ['01', '1']],
            'zero' => [['0', '00'], ['00', '0']],
            'decimal' => [['1', '1.0'], ['1.0', '1']],
            'exponent' => [['100', '1e2'], ['1e2', '100']],
        ];
    }

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

    /**
     * @param string[] $firstTags
     * @param string[] $secondTags
     */
    #[DataProvider('distinctTagSetsProvider')]
    public function testTaggedGetSetDistinctTags(array $firstTags, array $secondTags): void
    {
        $firstCache = $this->cacher->tags($firstTags);
        $secondCache = $this->cacher->tags($secondTags);

        $firstCache->set('user.1', 'first');
        $secondCache->set('user.1', 'second');

        $this->assertSame(
            'first',
            $firstCache->get('user.1')
        );

        $this->assertSame(
            'second',
            $secondCache->get('user.1')
        );
    }

    /**
     * @param string[] $firstTags
     * @param string[] $secondTags
     */
    #[DataProvider('equivalentTagSetsProvider')]
    public function testTaggedGetSetEquivalentTags(array $firstTags, array $secondTags): void
    {
        $this->cacher->tags($firstTags)->set('user.1', 'value');

        $this->assertSame(
            'value',
            $this->cacher->tags($secondTags)->get('user.1')
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

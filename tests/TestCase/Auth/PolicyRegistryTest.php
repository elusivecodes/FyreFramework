<?php
declare(strict_types=1);

namespace Tests\TestCase\Auth;

use Fyre\Auth\PolicyRegistry;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Utility\Inflector;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Models\OthersModel;
use Tests\Mock\Models\PostsModel;
use Tests\Mock\Policies\PostPolicy;

use function class_uses;

final class PolicyRegistryTest extends TestCase
{
    protected PolicyRegistry $policyRegistry;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(PolicyRegistry::class)
        );
    }

    public function testGetNamespaces(): void
    {
        $this->assertSame(
            [
                'Tests\Mock\Policies\\',
            ],
            $this->policyRegistry->getNamespaces()
        );
    }

    public function testHasNamespace(): void
    {
        $this->assertTrue(
            $this->policyRegistry->hasNamespace('Tests\Mock\Policies')
        );
    }

    public function testHasNamespaceInvalid(): void
    {
        $this->assertFalse(
            $this->policyRegistry->hasNamespace('Tests\Invalid')
        );
    }

    public function testMap(): void
    {
        $this->assertSame(
            $this->policyRegistry,
            $this->policyRegistry->map('Others', PostPolicy::class)
        );

        $policy = $this->policyRegistry->use('Others');

        $this->assertInstanceOf(PostPolicy::class, $policy);
    }

    public function testMapClassName(): void
    {
        $this->assertSame(
            $this->policyRegistry,
            $this->policyRegistry->map(OthersModel::class, PostPolicy::class)
        );

        $policy = $this->policyRegistry->use(OthersModel::class);

        $this->assertInstanceOf(PostPolicy::class, $policy);
    }

    public function testRemoveNamespace(): void
    {
        $this->assertSame(
            $this->policyRegistry,
            $this->policyRegistry->removeNamespace('Tests\Mock\Policies')
        );

        $this->assertFalse(
            $this->policyRegistry->hasNamespace('Tests\Mock\Policies')
        );
    }

    public function testRemoveNamespaceInvalid(): void
    {
        $this->assertSame(
            $this->policyRegistry,
            $this->policyRegistry->removeNamespace('Tests\Invalid')
        );
    }

    public function testUse(): void
    {
        $policy = $this->policyRegistry->use('Posts');

        $this->assertInstanceOf(PostPolicy::class, $policy);

        $this->assertSame(
            $this->policyRegistry->use('Posts'),
            $policy
        );
    }

    public function testUseAttribute(): void
    {
        $policy = $this->policyRegistry->use(OthersModel::class);

        $this->assertInstanceOf(PostPolicy::class, $policy);
    }

    public function testUseClassName(): void
    {
        $policy = $this->policyRegistry->use(PostsModel::class);

        $this->assertInstanceOf(PostPolicy::class, $policy);
    }

    public function testUseInvalid(): void
    {
        $policy = $this->policyRegistry->use('Invalid');

        $this->assertNull($policy);
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Inflector::class);
        $container->singleton(PolicyRegistry::class);

        $this->policyRegistry = $container->use(PolicyRegistry::class);

        $this->policyRegistry->addNamespace('Tests\Mock\Policies');
    }
}

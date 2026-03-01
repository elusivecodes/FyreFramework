<?php
declare(strict_types=1);

namespace Tests\TestCase\Security;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Security\ContentSecurityPolicy;
use Fyre\Security\Policy;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class ContentSecurityPolicyTest extends TestCase
{
    protected ContentSecurityPolicy $csp;

    public function testCreatePolicy(): void
    {
        $policy = $this->csp->createPolicy('default', [
            'default-src' => 'self',
            'child-src' => 'none',
        ]);

        $policy = $this->csp->getPolicy('default');

        $this->assertInstanceOf(
            Policy::class,
            $policy
        );

        $this->assertSame(
            'default-src \'self\'; child-src \'none\';',
            $policy->getHeaderString()
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(ContentSecurityPolicy::class)
        );
    }

    public function testGetInvalid(): void
    {
        $this->assertNull(
            $this->csp->getPolicy('invalid')
        );
    }

    public function testGetPolicies(): void
    {
        $this->csp->createPolicy('default', []);
        $this->csp->createPolicy('report', []);

        $policies = $this->csp->getPolicies();

        $this->assertInstanceOf(
            Policy::class,
            $policies['default']
        );

        $this->assertInstanceOf(
            Policy::class,
            $policies['report']
        );
    }

    public function testGetPolicy(): void
    {
        $this->csp->createPolicy('default', []);

        $policy = $this->csp->getPolicy('default');

        $this->assertInstanceOf(
            Policy::class,
            $policy
        );
    }

    public function testHasPolicy(): void
    {
        $this->csp->createPolicy('default', []);

        $this->assertTrue(
            $this->csp->hasPolicy('default')
        );
    }

    public function testHasPolicyInvalid(): void
    {
        $this->assertFalse(
            $this->csp->hasPolicy('invalid')
        );
    }

    public function testSetPolicy(): void
    {
        $policy = new Policy();

        $this->assertSame(
            $this->csp,
            $this->csp->setPolicy('test', $policy)
        );

        $this->assertSame(
            $policy,
            $this->csp->getPolicy('test')
        );
    }

    public function testSetReportTo(): void
    {
        $this->assertSame(
            $this->csp,
            $this->csp->setReportTo([
                'group' => 'csp-endpoint',
                'max_age' => '10886400',
                'endpoints' => [
                    [
                        'url' => 'https://test.com/csp-report',
                    ],
                ],
            ])
        );

        $this->assertSame(
            [
                'group' => 'csp-endpoint',
                'max_age' => '10886400',
                'endpoints' => [
                    [
                        'url' => 'https://test.com/csp-report',
                    ],
                ],
            ],
            $this->csp->getReportTo()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Config::class);

        $this->csp = $container->build(ContentSecurityPolicy::class);
    }
}

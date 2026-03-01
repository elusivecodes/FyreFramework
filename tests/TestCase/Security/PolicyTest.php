<?php
declare(strict_types=1);

namespace Tests\TestCase\Security;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Security\Policy;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class PolicyTest extends TestCase
{
    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Policy::class)
        );
    }

    public function testGetDirective(): void
    {
        $policy = new Policy([
            'default-src' => [
                'self',
                'https://test.com/',
            ],
        ]);

        $this->assertSame(
            [
                'self',
                'https://test.com/',
            ],
            $policy->getDirective('default-src')
        );
    }

    public function testGetDirectiveInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CSP directive `invalid` is not valid.');

        $policy = new Policy();

        $policy->getDirective('invalid');
    }

    public function testHasDirective(): void
    {
        $policy = new Policy([
            'default-src' => 'self',
        ]);

        $this->assertTrue(
            $policy->hasDirective('default-src')
        );
    }

    public function testHasDirectiveFalse(): void
    {
        $policy = new Policy();

        $this->assertFalse(
            $policy->hasDirective('default-src')
        );
    }

    public function testHasDirectiveInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CSP directive `invalid` is not valid.');

        $policy = new Policy();

        $policy->hasDirective('invalid');
    }

    public function testWithDirective(): void
    {
        $policy1 = new Policy();
        $policy2 = $policy1->withDirective('default-src', 'self');

        $this->assertSame(
            '',
            $policy1->getHeaderString()
        );

        $this->assertSame(
            'default-src \'self\';',
            $policy2->getHeaderString()
        );
    }

    public function testWithDirectiveArray(): void
    {
        $policy1 = new Policy();
        $policy2 = $policy1->withDirective('default-src', [
            'self',
            'https://test.com/',
        ]);

        $this->assertSame(
            '',
            $policy1->getHeaderString()
        );

        $this->assertSame(
            'default-src \'self\' https://test.com/;',
            $policy2->getHeaderString()
        );
    }

    public function testWithDirectiveFalse(): void
    {
        $policy1 = new Policy([
            'upgrade-insecure-requests' => true,
        ]);
        $policy2 = $policy1->withDirective('upgrade-insecure-requests', false);

        $this->assertSame(
            'upgrade-insecure-requests;',
            $policy1->getHeaderString()
        );

        $this->assertSame(
            '',
            $policy2->getHeaderString()
        );
    }

    public function testWithDirectiveInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CSP directive `invalid` is not valid.');

        $policy = new Policy();

        $policy->withDirective('invalid', true);
    }

    public function testWithDirectiveMerge(): void
    {
        $policy1 = new Policy([
            'default-src' => 'self',
        ]);
        $policy2 = $policy1->withDirective('default-src', 'https://test.com/');

        $this->assertSame(
            'default-src \'self\';',
            $policy1->getHeaderString()
        );

        $this->assertSame(
            'default-src \'self\' https://test.com/;',
            $policy2->getHeaderString()
        );
    }

    public function testWithDirectiveTrue(): void
    {
        $policy1 = new Policy();
        $policy2 = $policy1->withDirective('upgrade-insecure-requests', true);

        $this->assertSame(
            '',
            $policy1->getHeaderString()
        );

        $this->assertSame(
            'upgrade-insecure-requests;',
            $policy2->getHeaderString()
        );
    }

    public function testWithDirectiveUnique(): void
    {
        $policy1 = new Policy([
            'default-src' => 'self',
        ]);
        $policy2 = $policy1->withDirective('default-src', 'self');

        $this->assertSame(
            'default-src \'self\';',
            $policy1->getHeaderString()
        );

        $this->assertSame(
            'default-src \'self\';',
            $policy2->getHeaderString()
        );
    }

    public function testWithoutDirective(): void
    {
        $policy1 = new Policy([
            'upgrade-insecure-requests' => true,
        ]);
        $policy2 = $policy1->withoutDirective('upgrade-insecure-requests');

        $this->assertSame(
            'upgrade-insecure-requests;',
            $policy1->getHeaderString()
        );

        $this->assertSame(
            '',
            $policy2->getHeaderString()
        );
    }

    public function testWithoutDirectiveInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CSP directive `invalid` is not valid.');

        $policy = new Policy();

        $policy->withoutDirective('invalid');
    }
}

<?php
declare(strict_types=1);

namespace Tests\TestCase\Security;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Security\CsrfProtection;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class CsrfProtectionTest extends TestCase
{
    protected CsrfProtection $csrfProtection;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(CsrfProtection::class)
        );
    }

    public function testGetCookieToken(): void
    {
        $this->assertSame(
            $this->csrfProtection->getCookieToken(),
            $this->csrfProtection->getCookieToken()
        );
    }

    public function testGetField(): void
    {
        $this->assertSame(
            'csrf_token',
            $this->csrfProtection->getField()
        );
    }

    public function testGetFormToken(): void
    {
        $this->assertNotSame(
            $this->csrfProtection->getFormToken(),
            $this->csrfProtection->getFormToken()
        );

        $this->assertNotSame(
            $this->csrfProtection->getCookieToken(),
            $this->csrfProtection->getFormToken()
        );
    }

    public function testGetHeader(): void
    {
        $this->assertSame(
            'Csrf-Token',
            $this->csrfProtection->getHeader()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Config::class);

        $container->use(Config::class)->set('Csrf.salt', 'l2wyQow3eTwQeTWcfZnlgU8FnbiWljpGjQvNP2pL');

        $this->csrfProtection = $container->build(CsrfProtection::class);
    }
}

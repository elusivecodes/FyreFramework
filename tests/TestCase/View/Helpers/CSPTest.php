<?php
declare(strict_types=1);

namespace Tests\Helpers;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Http\ServerRequest;
use Fyre\Security\ContentSecurityPolicy;
use Fyre\View\CellRegistry;
use Fyre\View\HelperRegistry;
use Fyre\View\TemplateLocator;
use Fyre\View\View;
use Override;
use PHPUnit\Framework\TestCase;

final class CSPTest extends TestCase
{
    protected ContentSecurityPolicy $csp;

    protected View $view;

    public function testScriptNonce(): void
    {
        $this->csp->createPolicy(ContentSecurityPolicy::DEFAULT, []);

        $nonce = $this->view->Csp->scriptNonce();

        $policy = $this->csp->getPolicy(ContentSecurityPolicy::DEFAULT);

        $this->assertMatchesRegularExpression(
            '/^[a-f0-9]{40}$/',
            $nonce
        );

        $this->assertSame(
            'script-src \'nonce-'.$nonce.'\';',
            $policy->getHeaderString()
        );
    }

    public function testStyleNonce(): void
    {
        $this->csp->createPolicy(ContentSecurityPolicy::DEFAULT, []);

        $nonce = $this->view->Csp->styleNonce();

        $policy = $this->csp->getPolicy(ContentSecurityPolicy::DEFAULT);

        $this->assertMatchesRegularExpression(
            '/^[a-f0-9]{40}$/',
            $nonce
        );

        $this->assertSame(
            'style-src \'nonce-'.$nonce.'\';',
            $policy->getHeaderString()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Config::class);
        $container->singleton(TemplateLocator::class);
        $container->singleton(HelperRegistry::class);
        $container->singleton(CellRegistry::class);
        $container->singleton(ContentSecurityPolicy::class);

        $this->csp = $container->use(ContentSecurityPolicy::class);

        $request = $container->build(ServerRequest::class);

        $this->view = $container->build(View::class, ['request' => $request]);
    }
}

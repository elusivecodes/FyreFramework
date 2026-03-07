<?php
declare(strict_types=1);

namespace Fyre\View\Helpers;

use Fyre\Security\ContentSecurityPolicy;
use Fyre\View\Helper;
use Fyre\View\View;

use function hash;
use function random_bytes;

/**
 * Integrates Content Security Policy (CSP) into views.
 *
 * Note: Nonces are added to all configured CSP policies.
 */
class CspHelper extends Helper
{
    protected string|null $scriptNonce = null;

    protected string|null $styleNonce = null;

    /**
     * Constructs a CspHelper.
     *
     * @param ContentSecurityPolicy $csp The ContentSecurityPolicy.
     * @param View $view The View.
     * @param array<string, mixed> $options The helper options.
     */
    public function __construct(
        protected ContentSecurityPolicy $csp,
        View $view,
        array $options = []
    ) {
        parent::__construct($view, $options);
    }

    /**
     * Generates a script nonce.
     *
     * @return string The script nonce.
     */
    public function scriptNonce(): string
    {
        return $this->scriptNonce ??= $this->addNonce('script-src');
    }

    /**
     * Generates a style nonce.
     *
     * @return string The style nonce.
     */
    public function styleNonce(): string
    {
        return $this->styleNonce ??= $this->addNonce('style-src');
    }

    /**
     * Adds a nonce for a directive.
     *
     * Note: This mutates the {@see ContentSecurityPolicy} instance by setting updated
     * policies.
     *
     * @param string $directive The directive.
     * @return string The nonce.
     */
    protected function addNonce(string $directive): string
    {
        $nonce = static::generateNonce();
        $value = 'nonce-'.$nonce;

        $policies = $this->csp->getPolicies();

        foreach ($policies as $key => $policy) {
            $policy = $policy->withDirective($directive, $value);

            $this->csp->setPolicy($key, $policy);
        }

        return $nonce;
    }

    /**
     * Generates a nonce.
     *
     * @return string The nonce.
     */
    protected static function generateNonce(): string
    {
        return hash('sha1', random_bytes(12));
    }
}

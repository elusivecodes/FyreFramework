<?php
declare(strict_types=1);

namespace Fyre\Security;

use Fyre\Core\Config;
use Fyre\Core\Traits\DebugTrait;
use Psr\Http\Message\ResponseInterface;

use function json_encode;

use const JSON_UNESCAPED_SLASHES;

/**
 * Builds and applies Content Security Policy (CSP) headers.
 *
 * Supports both enforce (`Content-Security-Policy`) and report-only
 * (`Content-Security-Policy-Report-Only`) modes.
 */
class ContentSecurityPolicy
{
    use DebugTrait;

    public const DEFAULT = 'default';

    public const REPORT = 'report';

    protected const POLICY_HEADERS = [
        'default' => 'Content-Security-Policy',
        'report' => 'Content-Security-Policy-Report-Only',
    ];

    /**
     * @var array<string, Policy>
     */
    protected array $policies = [];

    /**
     * @var array<string, mixed>
     */
    protected array $reportTo = [];

    /**
     * Constructs a ContentSecurityPolicy.
     *
     * @param Config $config The Config.
     */
    public function __construct(Config $config)
    {
        $options = $config->get('Csp', []);

        foreach (static::POLICY_HEADERS as $key => $header) {
            if (!isset($options[$key])) {
                continue;
            }

            $this->createPolicy($key, $options[$key]);
        }

        if (isset($options['reportTo'])) {
            $this->setReportTo($options['reportTo']);
        }
    }

    /**
     * Adds ContentSecurityPolicy headers to a Response.
     *
     * Note: Only configured policies with non-empty header strings are emitted. If
     * `Report-To` data is present, it is JSON-encoded and added as a `Report-To` header.
     *
     * @param ResponseInterface $response The Response.
     * @return ResponseInterface The new Response.
     */
    public function addHeaders(ResponseInterface $response): ResponseInterface
    {
        foreach (static::POLICY_HEADERS as $key => $header) {
            if (!isset($this->policies[$key])) {
                continue;
            }

            $value = $this->policies[$key]->getHeaderString();

            if (!$value) {
                continue;
            }

            $response = $response->withHeader($header, $value);
        }

        if ($this->reportTo !== []) {
            $response = $response->withHeader('Report-To', (string) json_encode($this->reportTo, JSON_UNESCAPED_SLASHES));
        }

        return $response;
    }

    /**
     * Clears all policies.
     */
    public function clear(): void
    {
        $this->policies = [];
    }

    /**
     * Creates a policy.
     *
     * Note: Only policies with keys defined in {@see self::POLICY_HEADERS} are emitted by
     * {@see self::addHeaders()}.
     *
     * @param string $key The policy key.
     * @param array<string, bool|string|string[]> $directives The policy directives.
     * @return Policy The Policy.
     */
    public function createPolicy(string $key, array $directives = []): Policy
    {
        return $this->policies[$key] = new Policy($directives);
    }

    /**
     * Returns all policies.
     *
     * @return array<string, Policy> The policies.
     */
    public function getPolicies(): array
    {
        return $this->policies;
    }

    /**
     * Returns a policy.
     *
     * @param string $key The policy key.
     * @return Policy|null The Policy.
     */
    public function getPolicy(string $key): Policy|null
    {
        return $this->policies[$key] ?? null;
    }

    /**
     * Returns the Report-To values.
     *
     * @return array<string, mixed> The Report-To values.
     */
    public function getReportTo(): array
    {
        return $this->reportTo;
    }

    /**
     * Checks whether a policy exists.
     *
     * @param string $key The policy key.
     * @return bool Whether the policy exists.
     */
    public function hasPolicy(string $key): bool
    {
        return isset($this->policies[$key]);
    }

    /**
     * Sets a policy.
     *
     * @param string $key The policy key.
     * @param Policy $policy The Policy.
     * @return static The ContentSecurityPolicy instance.
     */
    public function setPolicy(string $key, Policy $policy): static
    {
        $this->policies[$key] = $policy;

        return $this;
    }

    /**
     * Sets the Report-To values.
     *
     * @param array<string, mixed> $reportTo The Report-To values.
     * @return static The ContentSecurityPolicy instance.
     */
    public function setReportTo(array $reportTo): static
    {
        $this->reportTo = $reportTo;

        return $this;
    }
}

# Security

Security covers CSRF protection, Content Security Policy, encryption, and rate limiting.

## Table of Contents

- [Start here](#start-here)
- [Security overview](#security-overview)
- [Pages in this section](#pages-in-this-section)

## Start here

Pick a path based on what you’re trying to protect:

- **Browser form submissions**: start with [CSRF](csrf.md)
- **HTML response hardening**: start with [Content Security Policy (CSP)](csp.md)
- **Abusive traffic**: start with [Rate Limiting](rate-limiting.md)
- **Sensitive values outside the process**: start with [Encryption](encryption.md)

## Security overview

Most applications use the security section in two ways:

- **At the HTTP boundary**: middleware and response headers protect requests and responses
- **At storage boundaries**: encryption protects values that leave the process

The main pieces are straightforward:

- `CsrfProtection` issues and validates CSRF tokens
- `ContentSecurityPolicy` builds CSP headers and works with nonce helpers
- `RateLimiterMiddleware` throttles requests based on identifiers and cost
- `EncryptionManager` gives you named encrypters for encrypting and decrypting values

## Pages in this section

- [CSRF](csrf.md) - protect state-changing requests with cookie and form/header tokens
- [Content Security Policy (CSP)](csp.md) - build CSP headers, report-only policies, and view nonces
- [Rate Limiting](rate-limiting.md) - throttle requests by identifier, limit, and cost
- [Encryption](encryption.md) - encrypt and decrypt values with named encrypters

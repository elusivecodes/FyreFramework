# Security

Security covers CSRF protection, Content Security Policy, encryption, and rate limiting.

## Table of Contents

- [Choose a control](#choose-a-control)
- [Security overview](#security-overview)
- [Pages in this section](#pages-in-this-section)

## Choose a control

| Risk | Control |
| --- | --- |
| forged browser requests that use a visitor's authenticated session | [CSRF](csrf.md) |
| scripts, styles, or other resources loaded outside an approved policy | [Content Security Policy (CSP)](csp.md) |
| excessive requests from a client or application-defined identity | [Rate Limiting](rate-limiting.md) |
| sensitive values stored or transported outside the process | [Encryption](encryption.md) |

## Security overview

CSRF, CSP, and rate limiting protect different parts of the HTTP boundary and may be combined. Encryption protects values at storage or transport boundaries; it does not replace access control, secure transport, or password hashing.

## Pages in this section

- [CSRF](csrf.md) - protect state-changing requests with cookie and form/header tokens
- [Content Security Policy (CSP)](csp.md) - build CSP headers, report-only policies, and view nonces
- [Rate Limiting](rate-limiting.md) - throttle requests by identifier, limit, and cost
- [Encryption](encryption.md) - encrypt and decrypt values with named encrypters

# Security

Security docs cover request-level primitives for protecting state-changing requests, tightening browser security boundaries, encrypting application data, and throttling abusive traffic.

## Table of Contents

- [Start here](#start-here)
- [Security overview](#security-overview)
- [Pages in this section](#pages-in-this-section)

## Start here

Pick a path based on what you’re trying to protect:

- **Browser form submissions**: start with [CSRF](csrf.md) (tokens, token sources, and middleware behavior).
- **HTML response hardening**: start with [Content Security Policy (CSP)](csp.md) (policies, report-only mode, and response header emission).
- **Abusive traffic**: start with [Rate Limiting](rate-limiting.md) (strategies, identifiers/cost, and middleware integration).
- **Sensitive values outside the process**: start with [Encryption](encryption.md) (encrypters, key handling, and common workflows).

## Security overview

In Fyre, many security features are applied at the HTTP boundary (middleware and response headers), while keeping the core APIs usable anywhere services are available.

Most applications will apply these features centrally through [HTTP Middleware](../http/middleware.md), and then read request attributes or helpers when rendering HTML (for example, CSRF and CSP nonces).

## Pages in this section

- [CSRF](csrf.md) — CSRF tokens, token sources, and middleware behavior.
- [Content Security Policy (CSP)](csp.md) — CSP policies, report-only mode, and response header emission.
- [Rate Limiting](rate-limiting.md) — limiter strategies, identifiers and cost, and middleware integration.
- [Encryption](encryption.md) — encrypter handlers, shared instances, and common encryption workflows.

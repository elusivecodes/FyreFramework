# Auth

Auth covers authentication (who the user is), authorization (what they can do), and the middleware that attaches auth context to HTTP requests.

## Table of Contents

- [Start here](#start-here)
- [Pages in this section](#pages-in-this-section)

## Start here

At a high level, the auth subsystem is split into:

- **Authentication:** `Auth` is the usual entry point for authentication. It resolves and stores the current user, typically via one or more authenticators.
- **Authorization:** `Auth::access()` returns an `Access` instance that evaluates rules and policies for the resolved user.
- **Middleware integration:** auth middleware runs authentication for the current request, attaches `auth` and `user` request attributes, and provides route-level guards such as `authenticated`, `unauthenticated`, and `can`.

A typical path through the subsystem looks like this:

- Start with [Authentication](authentication.md) to configure authenticators and login/logout flows.
- Add [Auth Middleware](middleware.md) to establish request auth context and guard routes.
- Use [Authorization](authorization.md) when you need named rules or policy-based access checks.

## Pages in this section

- [Authentication](authentication.md) — configure authenticators and resolve the current user.
- [Auth Middleware](middleware.md) — run authentication on requests and guard routes.
- [Authorization](authorization.md) — define access rules/policies and enforce authorization.

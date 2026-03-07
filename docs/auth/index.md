# Auth

Auth covers authentication (who the user is), authorization (what they can do), and the middleware that attaches auth context to HTTP requests.

## Table of Contents

- [Auth overview](#auth-overview)
- [Pages in this section](#pages-in-this-section)

## Auth overview

At a high level, the auth subsystem is split into:

- **Authentication:** `Auth` is the usual entry point for authentication. It resolves and stores the current user, typically via one or more authenticators.
- **Authorization:** `Auth::access()` returns an `Access` instance that evaluates rules and policies for the resolved user.
- **Middleware integration:** auth middleware runs authentication for the current request, attaches `auth` and `user` request attributes, and provides route-level guards such as `authenticated`, `unauthenticated`, and `can`.

## Pages in this section

- [Authentication](authentication.md) — configure authenticators and resolve the current user.
- [Auth Middleware](middleware.md) — run authentication on requests and guard routes.
- [Authorization](authorization.md) — define access rules/policies and enforce authorization.

# Auth

🧭 Auth covers authentication (who the user is) and authorization (what they can do), plus the middleware that integrates both into the HTTP request lifecycle.

## Table of Contents

- [Start here](#start-here)
- [Auth overview](#auth-overview)
- [Pages in this section](#pages-in-this-section)

## Start here

Pick a path based on what you’re doing:

- **Logging users in and out**: start with [Authentication](authentication.md).
- **Protecting routes and endpoints**: start with [Auth Middleware](middleware.md).
- **Checking permissions**: start with [Authorization](authorization.md).

## Auth overview

At a high level, Auth is split into:

- **Authentication:** resolve a user for the current request (typically via one or more authenticators).
- **Authorization:** evaluate access rules and policies for the resolved user.
- **Middleware integration:** attach auth/user context to the request and provide route-level guards.

## Pages in this section

- [Authentication](authentication.md) — configure authenticators and resolve the current user.
- [Auth Middleware](middleware.md) — run authentication on requests and guard routes.
- [Authorization](authorization.md) — define access rules/policies and enforce authorization.

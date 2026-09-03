# Auth

Auth covers authentication (who the user is), authorization (what they can do), and the middleware that attaches auth context to HTTP requests.

## Table of Contents

- [Authentication flow](#authentication-flow)
- [Pages in this section](#pages-in-this-section)

## Authentication flow

Start by configuring how credentials identify a user in [Authentication](authentication.md), including optional session-based user impersonation. Add [Auth Middleware](middleware.md) to authenticate requests and make the current user available to application code. Then define and enforce application permissions with [Authorization](authorization.md).

Authentication establishes identity; authorization decides whether that identity may perform an action. Applications commonly use both, but their configuration and failure behavior remain separate.

## Pages in this section

- [Authentication](authentication.md) - configure authenticators and resolve the current user
- [Auth Middleware](middleware.md) - run authentication on requests and guard routes
- [Authorization](authorization.md) - define access rules and policies and enforce authorization

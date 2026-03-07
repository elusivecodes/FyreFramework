# Constraints

Fyre’s testing layer includes a set of PHPUnit constraints that power many of the higher-level assertion helpers provided by the testing traits.

## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [Available constraints](#available-constraints)
  - [Response](#response)
  - [Console](#console)
  - [Email](#email)
  - [Log](#log)
  - [Session](#session)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use constraints when you want to compose custom assertions with PHPUnit’s `assertThat()` (for example, when you’re not using a trait helper, or you want to assert against values you captured yourself).

In most test cases, prefer the trait assertion helpers (they are shorter and keep failure output consistent). Constraints are the lower-level building blocks.

## Quick start

Assert against a response object directly:

```php
use Fyre\TestSuite\Constraint\Response\StatusCode;

$this->assertThat($response, new StatusCode(200));
```

Assert against session data (for example, a snapshot of `$_SESSION`):

```php
use Fyre\TestSuite\Constraint\Session\SessionEquals;

$this->assertThat($_SESSION, new SessionEquals(1, 'Auth.user_id'));
```

## Available constraints

Constraints are grouped by output type under `Fyre\TestSuite\Constraint\*`.

### Response

Response constraints expect a response object (anything with the relevant methods, such as `getStatusCode()`, `getHeaderLine()`, and `getBody()`).

- `BodyContains`, `BodyNotContains`
- `BodyEquals`, `BodyNotEquals`
- `BodyEmpty`, `BodyNotEmpty`
- `ContentType`
- `CookieEquals`, `CookieSet`, `CookieNotSet`
- `File`
- `HeaderEquals`, `HeaderContains`, `HeaderSet`, `HeaderNotSet`, `HeaderNotContains`
- `StatusCode`, `StatusCodeBetween`

### Console

Console constraints are used for asserting captured console output and exit codes.

- `ContentsContains`, `ContentsNotContains`
- `ContentsContainsRow`
- `ContentsRegExp`
- `ContentsEmpty`
- `ExitCode`

### Email

Email constraints are used for asserting against captured sent messages.

- `MailCount`, `NoMailSent`
- `MailSentTo`, `MailSentFrom`, `MailSentWith`
- `MailSubjectContains`
- `MailBodyContains`
- `MailContainsAttachment`

### Log

Log constraints are used for asserting against captured log output.

- `LogIsEmpty`
- `LogMessage`
- `LogMessageContains`

### Session

Session constraints are used for asserting session values using dot-path keys.

- `SessionEquals`
- `SessionHasKey`, `SessionNotHasKey`
- `FlashMessageEquals`

## Behavior notes

A few behaviors are worth keeping in mind:

- Response-body constraints read via `$response->getBody()->getContents()`, which consumes the stream; repeated assertions may require rewinding or using a fresh body stream.

## Related

- [Testing](index.md)
- [`TestCase`](test-case.md)
- [Integration Testing](integration.md)
- [Console Testing](console.md)
- [Email Testing](mail.md)
- [Log Testing](logging.md)

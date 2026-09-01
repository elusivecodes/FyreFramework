# Constraints

Use these constraints when you want to build custom PHPUnit assertions with `assertThat()`.

Most tests should prefer the higher-level trait helpers, but the constraints are useful when you already have a response, console output, session snapshot, captured email, or log messages in hand.

## Table of Contents

- [Start here](#start-here)
- [Available constraints](#available-constraints)
  - [Response](#response)
  - [Console](#console)
  - [Email](#email)
  - [Log](#log)
  - [Session](#session)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Use constraints directly when you already have the value under test and do not need the state managed by a testing trait:

```php
use Fyre\TestSuite\Constraint\Response\StatusCode;
use Fyre\TestSuite\Constraint\Session\SessionEquals;

$this->assertThat($response, new StatusCode(200));

$this->assertThat(
    $_SESSION,
    new SessionEquals(1, 'Auth.user_id')
);
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

Console constraints assert against captured console output and exit codes.

- `ContentsContains`, `ContentsNotContains`
- `ContentsContainsRow`
- `ContentsRegExp`
- `ContentsEmpty`
- `ExitCode`

### Email

Email constraints assert against captured sent messages.

- `MailCount`, `NoMailSent`
- `MailSentTo`, `MailSentFrom`, `MailSentWith`
- `MailSubjectContains`
- `MailBodyContains`
- `MailContainsAttachment`

### Log

Log constraints assert against captured log output.

- `LogIsEmpty`
- `LogMessage`
- `LogMessageContains`

### Session

Session constraints assert session values using dot-path keys.

- `SessionEquals`
- `SessionHasKey`, `SessionNotHasKey`
- `FlashMessageEquals`

## Behavior notes

A few behaviors are worth keeping in mind:

- Response-body constraints cast the body stream to a string. Seekable response bodies are rewound by the stream, so repeated assertions inspect the full body without manual rewinding.

## Related

- [Testing](index.md)
- [`TestCase`](test-case.md)
- [Integration Testing](integration.md)
- [Console Testing](console.md)
- [Email Testing](mail.md)
- [Log Testing](logging.md)

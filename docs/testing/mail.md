# Email Testing

Use `EmailTestTrait` in PHPUnit tests to capture outgoing `Fyre\Mail\Email` messages and assert against what was sent, without delivering real email.

## Table of Contents

- [Purpose](#purpose)
- [How it works](#how-it-works)
- [Example: send and assert a single email](#example-send-and-assert-a-single-email)
- [Method guide](#method-guide)
  - [API summary](#api-summary)
  - [Counting and presence](#counting-and-presence)
  - [Recipients and headers](#recipients-and-headers)
  - [Subject](#subject)
  - [Body](#body)
  - [Attachments](#attachments)
  - [Message access](#message-access)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use `EmailTestTrait` in tests that send email through `MailManager`, then assert on recipients, subject, body, and attachments.

```php
use Fyre\TestSuite\TestCase;
use Fyre\TestSuite\Traits\EmailTestTrait;

final class PasswordResetMailTest extends TestCase
{
    use EmailTestTrait;
}
```

## How it works

`EmailTestTrait` swaps the configured mail handlers for a test mailer that stores sent messages in memory, then clears captured messages after each test.

- Re-registers the existing `Fyre\Mail\MailManager` configs with `className` forced to `Fyre\TestSuite\Mail\Handlers\TestMailer`.
- `TestMailer` captures sent `Fyre\Mail\Email` messages in memory.
- Captured messages are cleared after each test.
- Methods with an `At` suffix target a specific email by 1-based index (for example: “email #1”).

## Example: send and assert a single email

```php
use Fyre\Mail\MailManager;
use Fyre\TestSuite\TestCase;
use Fyre\TestSuite\Traits\EmailTestTrait;

final class PasswordResetMailTest extends TestCase
{
    use EmailTestTrait;

    public function testSendsPasswordResetEmail(): void
    {
        $mailer = $this->app->use(MailManager::class)->use();

        $mailer->email()
            ->setFrom('no-reply@example.com')
            ->setTo('user@example.com')
            ->setSubject('Reset your password')
            ->setBodyText('Use this link to reset your password.')
            ->setFormat(Email::TEXT)
            ->send();

        $this->assertMailCount(1);
        $this->assertMailSentTo('user@example.com');
        $this->assertMailSubjectContains('Reset your password');
        $this->assertMailContainsText('reset your password');
    }
}
```

## Method guide

Most examples assume you’re in a `TestCase` using `EmailTestTrait`.

### API summary

```text
assertMailCount(int $count, string $message = ''): void
assertNoMailSent(string $message = ''): void

assertMailSentTo(string $address, string $message = ''): void
assertMailSentToAt(string $address, int $at, string $message = ''): void
assertMailSentFrom(string $address, string $message = ''): void
assertMailSentFromAt(string $address, int $at, string $message = ''): void

assertMailSentWithBcc(string $address, string $message = ''): void
assertMailSentWithBccAt(string $address, int $at, string $message = ''): void
assertMailSentWithCc(string $address, string $message = ''): void
assertMailSentWithCcAt(string $address, int $at, string $message = ''): void
assertMailSentWithReplyTo(string $address, string $message = ''): void
assertMailSentWithReplyToAt(string $address, int $at, string $message = ''): void
assertMailSentWithSender(string $address, string $message = ''): void
assertMailSentWithSenderAt(string $address, int $at, string $message = ''): void

assertMailSubjectContains(string $needle, string $message = ''): void
assertMailSubjectContainsAt(string $needle, int $at, string $message = ''): void

assertMailContains(string $needle, string $message = ''): void
assertMailContainsAt(string $needle, int $at, string $message = ''): void
assertMailContainsText(string $needle, string $message = ''): void
assertMailContainsTextAt(string $needle, int $at, string $message = ''): void
assertMailContainsHtml(string $needle, string $message = ''): void
assertMailContainsHtmlAt(string $needle, int $at, string $message = ''): void

assertMailContainsAttachment(string $filename, string $message = ''): void
assertMailContainsAttachmentAt(string $filename, int $at, string $message = ''): void

getMessages(int|null $at = null): array
```

### Counting and presence

#### **Assert the number of sent emails** (`assertMailCount()`)

Asserts that exactly `$count` emails were sent.

Arguments:
- `$count` (`int`): the expected number of emails.
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailCount(1);
```

#### **Assert that no emails were sent** (`assertNoMailSent()`)

Asserts that no emails were sent.

Arguments:
- `$message` (`string`): the message to display on failure.

```php
$this->assertNoMailSent();
```

### Recipients and headers

#### **Assert an email was sent to an address** (`assertMailSentTo()`)

Asserts that at least one captured email has the address in its `To` recipients.

Arguments:
- `$address` (`string`): the expected email address.
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailSentTo('user@example.com');
```

#### **Assert a specific email was sent to an address** (`assertMailSentToAt()`)

Asserts that email `#{$at}` has the address in its `To` recipients.

Arguments:
- `$address` (`string`): the expected email address.
- `$at` (`int`): the email index (1-based).
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailSentToAt('user@example.com', 1);
```

#### **Assert an email was sent from an address** (`assertMailSentFrom()`)

Asserts that at least one captured email has the address in its `From` header.

Arguments:
- `$address` (`string`): the expected email address.
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailSentFrom('no-reply@example.com');
```

#### **Assert a specific email was sent from an address** (`assertMailSentFromAt()`)

Asserts that email `#{$at}` has the address in its `From` header.

Arguments:
- `$address` (`string`): the expected email address.
- `$at` (`int`): the email index (1-based).
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailSentFromAt('no-reply@example.com', 1);
```

#### **Assert an email has a BCC recipient** (`assertMailSentWithBcc()`)

Asserts that at least one captured email has the address in its `Bcc` recipients.

Arguments:
- `$address` (`string`): the expected email address.
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailSentWithBcc('audit@example.com');
```

#### **Assert a specific email has a BCC recipient** (`assertMailSentWithBccAt()`)

Asserts that email `#{$at}` has the address in its `Bcc` recipients.

Arguments:
- `$address` (`string`): the expected email address.
- `$at` (`int`): the email index (1-based).
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailSentWithBccAt('audit@example.com', 1);
```

#### **Assert an email has a CC recipient** (`assertMailSentWithCc()`)

Asserts that at least one captured email has the address in its `Cc` recipients.

Arguments:
- `$address` (`string`): the expected email address.
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailSentWithCc('cc@example.com');
```

#### **Assert a specific email has a CC recipient** (`assertMailSentWithCcAt()`)

Asserts that email `#{$at}` has the address in its `Cc` recipients.

Arguments:
- `$address` (`string`): the expected email address.
- `$at` (`int`): the email index (1-based).
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailSentWithCcAt('cc@example.com', 1);
```

#### **Assert an email has a reply-to recipient** (`assertMailSentWithReplyTo()`)

Asserts that at least one captured email has the address in its `Reply-To` recipients.

Arguments:
- `$address` (`string`): the expected email address.
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailSentWithReplyTo('support@example.com');
```

#### **Assert a specific email has a reply-to recipient** (`assertMailSentWithReplyToAt()`)

Asserts that email `#{$at}` has the address in its `Reply-To` recipients.

Arguments:
- `$address` (`string`): the expected email address.
- `$at` (`int`): the email index (1-based).
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailSentWithReplyToAt('support@example.com', 1);
```

#### **Assert an email has a sender address** (`assertMailSentWithSender()`)

Asserts that at least one captured email has the address in its `Sender` header.

Arguments:
- `$address` (`string`): the expected email address.
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailSentWithSender('mailer@example.com');
```

#### **Assert a specific email has a sender address** (`assertMailSentWithSenderAt()`)

Asserts that email `#{$at}` has the address in its `Sender` header.

Arguments:
- `$address` (`string`): the expected email address.
- `$at` (`int`): the email index (1-based).
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailSentWithSenderAt('mailer@example.com', 1);
```

### Subject

#### **Assert an email subject contains a string** (`assertMailSubjectContains()`)

Asserts that at least one captured email subject contains `$needle`.

Arguments:
- `$needle` (`string`): the expected subject substring.
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailSubjectContains('Reset your password');
```

#### **Assert a specific email subject contains a string** (`assertMailSubjectContainsAt()`)

Asserts that email `#{$at}` has a subject containing `$needle`.

Arguments:
- `$needle` (`string`): the expected subject substring.
- `$at` (`int`): the email index (1-based).
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailSubjectContainsAt('Reset your password', 1);
```

### Body

#### **Assert an email body contains a string** (`assertMailContains()`)

Asserts that at least one captured email contains `$needle` in its full encoded body string.

Arguments:
- `$needle` (`string`): the expected substring.
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailContains('reset your password');
```

#### **Assert a specific email body contains a string** (`assertMailContainsAt()`)

Asserts that email `#{$at}` contains `$needle` in its full encoded body string.

Arguments:
- `$needle` (`string`): the expected substring.
- `$at` (`int`): the email index (1-based).
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailContainsAt('reset your password', 1);
```

#### **Assert an email text body contains a string** (`assertMailContainsText()`)

Asserts that at least one captured email contains `$needle` in its text body.

Arguments:
- `$needle` (`string`): the expected text substring.
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailContainsText('reset your password');
```

#### **Assert a specific email text body contains a string** (`assertMailContainsTextAt()`)

Asserts that email `#{$at}` contains `$needle` in its text body.

Arguments:
- `$needle` (`string`): the expected text substring.
- `$at` (`int`): the email index (1-based).
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailContainsTextAt('reset your password', 1);
```

#### **Assert an email HTML body contains a string** (`assertMailContainsHtml()`)

Asserts that at least one captured email contains `$needle` in its HTML body.

Arguments:
- `$needle` (`string`): the expected HTML substring.
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailContainsHtml('<a', 'Expected a link in the email body.');
```

#### **Assert a specific email HTML body contains a string** (`assertMailContainsHtmlAt()`)

Asserts that email `#{$at}` contains `$needle` in its HTML body.

Arguments:
- `$needle` (`string`): the expected HTML substring.
- `$at` (`int`): the email index (1-based).
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailContainsHtmlAt('<a', 1);
```

### Attachments

#### **Assert an email contains an attachment** (`assertMailContainsAttachment()`)

Asserts that at least one captured email has an attachment with the given `$filename`.

Arguments:
- `$filename` (`string`): the expected attachment filename.
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailContainsAttachment('invoice.pdf');
```

#### **Assert a specific email contains an attachment** (`assertMailContainsAttachmentAt()`)

Asserts that email `#{$at}` has an attachment with the given `$filename`.

Arguments:
- `$filename` (`string`): the expected attachment filename.
- `$at` (`int`): the email index (1-based).
- `$message` (`string`): the message to display on failure.

```php
$this->assertMailContainsAttachmentAt('invoice.pdf', 1);
```

### Message access

#### **Read captured email messages** (`getMessages()`)

Returns the captured `Email` messages. When `$at` is provided, this returns either an array with a single message (if that index exists) or an empty array.

Arguments:
- `$at` (`int|null`): the email index (1-based), or `null` for all messages.

```php
$messages = $this->getMessages();
$first = $this->getMessages(1);
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `...At()` methods use 1-based indexing. If the index is out of range, the assertion behaves like “no emails matched”.
- `assertMailContains()` searches the full encoded body string. Prefer `assertMailContainsText()` / `assertMailContainsHtml()` when you want to target a specific body type.
- Line endings are normalized before body comparisons (so `\r\n` and `\n` do not cause false negatives).

## Related

- [Testing](index.md)
- [Mail](../mail/index.md)
- [Emails](../mail/emails.md)

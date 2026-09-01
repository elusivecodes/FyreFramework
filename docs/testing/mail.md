# Email Testing

Use `EmailTestTrait` when your test sends email and you want to assert on what would have been delivered.

The trait swaps configured mailers to a test handler, captures sent messages in memory, and gives you helpers for recipients, subject, body, and attachments.

## Table of Contents

- [Start here](#start-here)
- [Sending and asserting email](#sending-and-asserting-email)
- [Assertion reference](#assertion-reference)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

The usual workflow is:

1. Use `EmailTestTrait` in your test case.
2. Run the code that sends email through `MailManager`.
3. Assert on the captured messages.

## Sending and asserting email

```php
use Fyre\Mail\Email;
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

## Assertion reference

Every assertion accepts an optional final `$message` argument for the PHPUnit failure message. Helpers ending in `At()` add a 1-based `$at` argument before it and inspect only that captured email.

Use `assertMailCount($count)` for an exact count and `assertNoMailSent()` when no email should have been delivered.

### Recipients and headers

| Match | Any captured email | Email at `$at` |
| --- | --- | --- |
| To recipient | `assertMailSentTo($address)` | `assertMailSentToAt($address, $at)` |
| From address | `assertMailSentFrom($address)` | `assertMailSentFromAt($address, $at)` |
| Cc recipient | `assertMailSentWithCc($address)` | `assertMailSentWithCcAt($address, $at)` |
| Bcc recipient | `assertMailSentWithBcc($address)` | `assertMailSentWithBccAt($address, $at)` |
| Reply-To address | `assertMailSentWithReplyTo($address)` | `assertMailSentWithReplyToAt($address, $at)` |
| Sender address | `assertMailSentWithSender($address)` | `assertMailSentWithSenderAt($address, $at)` |

### Subject, body, and attachments

| Match | Any captured email | Email at `$at` |
| --- | --- | --- |
| subject substring | `assertMailSubjectContains($needle)` | `assertMailSubjectContainsAt($needle, $at)` |
| complete encoded body | `assertMailContains($needle)` | `assertMailContainsAt($needle, $at)` |
| text body | `assertMailContainsText($needle)` | `assertMailContainsTextAt($needle, $at)` |
| HTML body | `assertMailContainsHtml($needle)` | `assertMailContainsHtmlAt($needle, $at)` |
| attachment filename | `assertMailContainsAttachment($filename)` | `assertMailContainsAttachmentAt($filename, $at)` |

`getMessages()` returns every captured `Email`. Passing a 1-based index returns an array containing that message, or an empty array when it does not exist.

## Behavior notes

- `...At()` methods use 1-based indexing. If the index is out of range, the assertion behaves like “no emails matched”.
- `assertMailContains()` searches the full encoded body string. Prefer `assertMailContainsText()` / `assertMailContainsHtml()` when you want to target a specific body type.
- Captured messages are cloned when sent, so later changes to the original `Email` do not alter the recorded message.
- Captured messages are cleared and the original `MailManager` configuration is restored after each test.
- Line endings are normalized before body comparisons (so `\r\n` and `\n` do not cause false negatives).

## Related

- [Testing](index.md)
- [Mail](../mail/index.md)
- [Emails](../mail/emails.md)

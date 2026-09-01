# Emails

Use `Fyre\Mail\Email` when you want to build a message, set recipients, and send it through a configured mailer.

## Table of Contents

- [Start here](#start-here)
- [Recipes](#recipes)
  - [Send a text email](#send-a-text-email)
  - [Send an HTML email](#send-an-html-email)
  - [Send text + HTML](#send-text--html)
  - [Add CC/BCC/Reply-To](#add-ccbccreply-to)
  - [Attach a file](#attach-a-file)
  - [Embed an inline image](#embed-an-inline-image)
- [Building an email](#building-an-email)
  - [Recipients](#recipients)
  - [Subject and body](#subject-and-body)
  - [Return path](#return-path)
  - [Format (text/html/both)](#format-texthtmlboth)
  - [Additional message options](#additional-message-options)
  - [Attachments](#attachments)
- [Troubleshooting](#troubleshooting)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Most email flows look like this:

1. Choose a mailer.
2. Create a new email with `email()`.
3. Set the sender, recipients, subject, and body.
4. Send it with `send()`.

The examples assume you already have a configured `Mailer` instance. If you do not, start with [Selecting a mailer](index.md#selecting-a-mailer).

## Recipes

### Send a text email

```php
$mailer->email()
    ->setFrom('no-reply@example.com', 'Example App')
    ->setTo('user@example.com')
    ->setSubject('Welcome')
    ->setBodyText("Thanks for signing up.\n")
    ->send();
```

### Send an HTML email

```php
use Fyre\Mail\Email;

$mailer->email()
    ->setFrom('no-reply@example.com', 'Example App')
    ->setTo('user@example.com')
    ->setSubject('Welcome')
    ->setBodyHtml('<p>Thanks for signing up.</p>')
    ->setFormat(Email::HTML)
    ->send();
```

### Send text + HTML

```php
use Fyre\Mail\Email;

$mailer->email()
    ->setFrom('no-reply@example.com', 'Example App')
    ->setTo('user@example.com')
    ->setSubject('Welcome')
    ->setBodyText("Thanks for signing up.\n")
    ->setBodyHtml('<p>Thanks for signing up.</p>')
    ->setFormat(Email::BOTH)
    ->send();
```

### Add CC/BCC/Reply-To

```php
$mailer->email()
    ->setFrom('no-reply@example.com', 'Example App')
    ->setTo('user@example.com')
    ->setCc('manager@example.com')
    ->setBcc('audit@example.com')
    ->setReplyTo('support@example.com')
    ->setSubject('Invoice available')
    ->setBodyText("Your invoice is ready.\n")
    ->send();
```

### Attach a file

```php
$mailer->email()
    ->setFrom('no-reply@example.com', 'Example App')
    ->setTo('user@example.com')
    ->setSubject('Invoice')
    ->setBodyText('See attached.')
    ->setAttachments([
        'invoice.pdf' => [
            'file' => '/path/to/invoice.pdf',
        ],
    ])
    ->send();
```

### Embed an inline image

```php
use Fyre\Mail\Email;

$mailer->email()
    ->setFrom('no-reply@example.com', 'Example App')
    ->setTo('user@example.com')
    ->setSubject('Welcome')
    ->setBodyHtml('<p><img src="cid:logo"></p>')
    ->setAttachments([
        'logo.png' => [
            'file' => '/path/to/logo.png',
            'contentId' => 'logo',
        ],
    ])
    ->setFormat(Email::HTML)
    ->send();
```

## Building an email

An `Email` holds the message you are building: recipients, headers, body, and attachments.

Most examples in this section assume you already have an `$email` instance:

```php
$email = $mailer->email();
```

### Recipients

Use `setFrom()` to set the `From` address and `setTo()` to set primary recipients. For additional headers, use `setCc()`, `setBcc()`, and `setReplyTo()`.

The `set*()` methods replace the existing list; the `add*()` methods append a single address.

```php
$email->setTo([
    'user@example.com' => 'Example User',
    'other@example.com',
]);
```

### Subject and body

Use `setSubject()` for the subject line and `setBodyText()` / `setBodyHtml()` for the message body.

Setting an HTML body does not automatically change the message format. Use `setFormat(Email::HTML)` or `Email::BOTH` when you want HTML output.

### Return path

Use `setReturnPath()` when you want a separate envelope sender address (for example, a dedicated bounces mailbox). When sending via SMTP, this value is used for the envelope sender when present.

```php
$email->setReturnPath('bounces@example.com');
```

### Format (text/html/both)

Use `setFormat()` with:

- `Email::TEXT`
- `Email::HTML`
- `Email::BOTH`

### Additional message options

Use `setSender()` when the address responsible for sending the message differs from the `From` address. You can also request a read receipt, set a priority from `1` to `5`, change the message charset, or add trusted custom headers.

```php
$email
    ->setSender('mailer@example.com')
    ->setReadReceipt('receipts@example.com')
    ->setPriority(1)
    ->setCharset('utf-8')
    ->setHeaders([
        'X-Campaign' => 'welcome',
    ]);
```

Do not pass untrusted values directly to `setHeaders()`.

### Attachments

To attach files, use `setAttachments()` or `addAttachments()`. Each attachment is keyed by filename and supports:

- `file`: a filesystem path to read, or
- `content`: raw file bytes
- `mimeType`: optional; auto-detected when missing
- `contentId`: optional; marks an attachment as inline (for example an `<img src="cid:...">` in HTML)
- `disposition`: optional; defaults to `inline` when `contentId` is set, otherwise `attachment`

Each attachment must provide either `file` or `content`. If `file` is used, it should point to a readable file.

See [Attach a file](#attach-a-file) for a complete example.

#### Inline attachments

Inline attachments work by setting a `contentId`, then referencing it from HTML using a `cid:` URL.

See [Embed an inline image](#embed-an-inline-image) for a complete example.

## Troubleshooting

- **HTML renders as plain text**: set `setFormat(Email::HTML)` or `setFormat(Email::BOTH)` (setting `setBodyHtml()` alone does not change the format).
- **“Email sending must have a valid recipient.”**: you built an email with no valid recipients. Invalid addresses are ignored; ensure at least one recipient passes `FILTER_VALIDATE_EMAIL`.
- **Inline image doesn’t show**: make sure your HTML uses `cid:your-id`, your attachment uses the same `contentId`, and you are sending HTML (`Email::HTML` or `Email::BOTH`).
- **Attachment error**: each attachment must include either readable `file` or string `content` data.

## Behavior notes

- Email addresses are validated with `FILTER_VALIDATE_EMAIL`. Invalid addresses are ignored when building recipient lists; sending fails if the final recipient set is empty.
- BCC addresses set with `setBcc()` are used as transport recipients but are excluded from rendered message headers.
- When sending via SMTP, the envelope sender uses `setReturnPath()` when set; otherwise it uses `setFrom()`.
- `Email` defaults its `charset` to the mailer `charset` option (default `utf-8`). When `App.charset` is set and differs, the body is converted when generating the final message.

## Related

- [Mail](index.md)
- [Email Testing](../testing/mail.md)

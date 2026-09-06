# Mail

Use the mail subsystem when you want to configure one or more mailers and send email through them.

Most applications configure a default mailer for production and a debug or test mailer for local development.

## Table of Contents

- [Start here](#start-here)
- [Configuring mailers](#configuring-mailers)
  - [Common mailer options](#common-mailer-options)
  - [Example configuration](#example-configuration)
- [Built-in mailer handlers](#built-in-mailer-handlers)
  - [SMTP](#smtp)
  - [Sendmail](#sendmail)
  - [Debug](#debug)
- [Selecting a mailer](#selecting-a-mailer)
- [Building one-off mailers](#building-one-off-mailers)
- [Managing mailer configurations](#managing-mailer-configurations)
- [Sending emails](#sending-emails)
- [Troubleshooting](#troubleshooting)
- [Related](#related)

## Start here

Use this page to configure SMTP, sendmail, or debug transports. [Emails](emails.md) covers recipients, body formats, attachments, and sending; [Email Testing](../testing/mail.md) covers captured messages and assertions.

For a typical application, configure the production transport as `default` and a debug transport for local development.

## Configuring mailers

Mailer configuration is read from the `Mail` key in your config (see [Config](../core/config.md)). Each named mailer config is an options array passed to the selected handler.

### Common mailer options

These options apply to all mailer handlers:

- `className` (`string`): the handler class name to build (must extend `Mailer`)
- `charset` (`string`): default `utf-8`
- `client` (`string|null`): default `null` (used as the client hostname for SMTP `HELO`/`EHLO` when set)

Other options depend on the selected handler.

### Example configuration

#### STARTTLS example (port `587`)

```php
use Fyre\Mail\Handlers\DebugMailer;
use Fyre\Mail\Handlers\SmtpMailer;

return [
    'Mail' => [
        'default' => [
            'className' => SmtpMailer::class,
            'host' => 'smtp.example.com',
            'port' => 587,
            'tls' => true,
            'auth' => true,
            'username' => 'user',
            'password' => 'secret',
        ],
        'debug' => [
            'className' => DebugMailer::class,
        ],
    ],
];
```

#### Implicit TLS example (SMTPS on port `465`)

```php
use Fyre\Mail\Handlers\SmtpMailer;

return [
    'Mail' => [
        'default' => [
            'className' => SmtpMailer::class,
            'host' => 'tls://smtp.example.com',
            'port' => 465,
            'auth' => true,
            'username' => 'user',
            'password' => 'secret',
        ],
    ],
];
```

## Built-in mailer handlers

The options below are specific to the built-in handler classes under `Fyre\Mail\Handlers\*`.

### SMTP

Implemented by `SmtpMailer`. Sends mail via SMTP.

- `host` (`string`): default `127.0.0.1`
- `port` (`int|string`): default `25`
- `username` (`string|null`): default `null`
- `password` (`string|null`): default `null`
- `auth` (`bool`): default `false`
- `tls` (`bool`): default `false` (enables `STARTTLS`)
- `dsn` (`bool`): default `false` (adds DSN hints to `RCPT TO`)
- `keepAlive` (`bool`): default `false` (reuses the SMTP connection across sends)

Once the SMTP server accepts the message, `QUIT` or `RSET` failures close the connection without failing the send. A subsequent send reconnects.

If the SMTP exchange fails before acceptance is confirmed, the connection is closed and the original exception propagates. The same mailer can reconnect on a later send; it does not automatically retry the failed message.

When `auth` is enabled, `username` and `password` must be non-empty strings.

`tls=true` enables `STARTTLS`. This mailer does not automatically secure the connection based on port; on most servers, use port `587` for `STARTTLS`.

For implicit TLS (SMTPS), prefix `host` with `tls://` (or `ssl://`), use port `465`, and leave `tls` as `false`.

#### Security considerations

`SmtpMailer` verifies TLS certificates and peer names using PHP's configured certificate authority store. TLS negotiation failures stop the connection before authentication.

If your SMTP server uses a private certificate authority, configure PHP's OpenSSL certificate authority settings before enabling TLS.

### Sendmail

Implemented by `SendmailMailer`. Sends via PHP’s `mail()` function and has no handler-specific options.

### Debug

Implemented by `DebugMailer`. Captures outbound messages in memory for inspection:

- `getSentEmails(): array` returns captured messages as `['headers' => ..., 'body' => ...]` arrays.
- `clear(): void` resets the captured message list.

For test assertions, prefer [Email Testing](../testing/mail.md).

## Selecting a mailer

Use a mailer key to select which stored config to use. When no key is provided, `MailManager::DEFAULT` (`default`) is used.

```php
use Fyre\Mail\MailManager;

$mailers = app(MailManager::class);

$default = $mailers->use();
$debug = $mailers->use('debug');
```

`email($key)` creates a new `Email` from the selected mailer; see [Helpers](../core/helpers.md).

```php
email('debug')
    ->setTo('user@example.com')
    ->setSubject('Welcome')
    ->setBodyText("Hello!\n");
```

If you are using contextual injection, you can request a mailer key directly on a parameter:

```php
use Fyre\Core\Attributes\Mail;
use Fyre\Mail\Mailer;

function sendWelcome(#[Mail] Mailer $mailer): void
{
    $mailer->email()
        ->setFrom('no-reply@example.com', 'Example App')
        ->setTo('user@example.com')
        ->setSubject('Welcome')
        ->setBodyText("Hello!\n")
        ->send();
}
```

Pass a key to the attribute when you need a non-default mailer, for example `#[Mail('debug')]`.

For the attribute behavior and other contextual injection helpers, see [Contextual attributes](../core/contextual-attributes.md).

## Building one-off mailers

Use `build()` to construct a mailer directly from options without storing it under a key or sharing it. The options must include a `className` that extends `Mailer`.

```php
use Fyre\Mail\Handlers\SmtpMailer;

$mailer = $mailers->build([
    'className' => SmtpMailer::class,
    'host' => '127.0.0.1',
    'port' => 587,
    'tls' => true,
]);
```

## Managing mailer configurations

| Method | Purpose |
| --- | --- |
| `getConfig($key = null)` | read one configuration, or all configurations |
| `hasConfig($key = 'default')` | check whether a configuration exists |
| `isLoaded($key = 'default')` | check whether a mailer has been built |
| `setConfig($key, $options)` | add a runtime configuration |
| `unload($key = 'default')` | remove a configuration and its loaded mailer |
| `clear()` | remove every configuration and loaded mailer |

`setConfig()` throws when the key already exists. Unload an entry before replacing it.

## Sending emails

Create a message via `Mailer::email()` and send it via `Email::send()` (or directly via `Mailer::send()`). For a deeper guide to building messages, formats, and attachments, see [Emails](emails.md).

Set a valid `From` address and at least one recipient before sending. `Mailer::send()` throws a `MailException` when the recipient list is empty.

```php
$mailer = $mailers->use();

$mailer->email()
    ->setFrom('no-reply@example.com', 'Example App')
    ->setTo('user@example.com')
    ->setSubject('Welcome')
    ->setBodyText("Thanks for signing up.\n")
    ->send();
```

## Troubleshooting

- **SMTP connection failed**: confirm `host`/`port`, and ensure your chosen TLS mode matches your server.
- **STARTTLS doesn’t work**: use `host` without `tls://`, `port` usually `587`, and `tls=true`.
- **Implicit TLS (SMTPS) doesn’t work**: use `host` prefixed with `tls://` (or `ssl://`), `port` usually `465`, and `tls=false`.
- **SMTP authentication failed**: set `auth=true` and ensure `username`/`password` are set to non-empty strings.
- **Testing without sending real email**: use the debug mailer (`DebugMailer`) or the test tooling in [Email Testing](../testing/mail.md).

## Related

- [Config](../core/config.md)
- [Emails](emails.md)
- [Email Testing](../testing/mail.md)
- [Helpers](../core/helpers.md)
- [Contextual attributes](../core/contextual-attributes.md)

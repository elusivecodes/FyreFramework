# Mail

🧭 Mail covers configuring mailers, sending email messages, and selecting handlers (SMTP, sendmail, debug) for different environments.

Each mailer configuration selects a handler and provides options that control delivery behavior.

## Table of Contents

- [Start here](#start-here)
- [Purpose](#purpose)
- [Mental model](#mental-model)
- [Configuring mailers](#configuring-mailers)
  - [Base mailer options](#base-mailer-options)
  - [Example configuration](#example-configuration)
- [Built-in mailer handlers](#built-in-mailer-handlers)
  - [SMTP](#smtp)
  - [Sendmail](#sendmail)
  - [Debug](#debug)
- [Selecting a mailer](#selecting-a-mailer)
- [Building one-off mailers](#building-one-off-mailers)
- [Sending emails](#sending-emails)
- [Method guide](#method-guide)
  - [`MailManager`](#mailmanager)
  - [`Mailer`](#mailer)
  - [`DebugMailer`](#debugmailer)
- [Troubleshooting](#troubleshooting)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Pick a path based on what you’re doing:

- **Sending and building messages**: see [Emails](emails.md) (recipients, body format, attachments, return path).
- **Configuring transports**: continue on this page (SMTP, sendmail, debug mailers).
- **Testing email output**: see [Email Testing](../testing/mail.md) (debug mailer and assertions).

## Purpose

🎯 Mailers are a good fit when you need to:

- switch transports by environment (for example, SMTP in production and a debug mailer locally)
- isolate delivery settings with multiple mailer keys (separate hosts, credentials, or options)
- keep message-building code stable while swapping the underlying transport

## Mental model

🧠 `MailManager` loads mailer configurations from [Config](../core/config.md) (the `Mail` key) and provides `Mailer` instances by key.

- Each config entry must specify a `className` that extends `Mailer`.
- `MailManager::use()` returns one shared mailer instance per key.
- `MailManager::build()` creates a new mailer instance from options without storing or sharing it.

Mail is split into two layers:

- `Mailer` is the transport layer. Handlers implement `send()` and can create new messages via `email()`.
- `Email` is the message being built (recipients, subject, headers, body, attachments). See [Emails](emails.md).

## Configuring mailers

Mailer configuration is read from the `Mail` key in your config (see [Config](../core/config.md)). Each named mailer config is an options array passed to the selected handler.

### Base mailer options

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
- `port` (`int|string`): default `465`
- `username` (`string|null`): default `null`
- `password` (`string|null`): default `null`
- `auth` (`bool`): default `false`
- `tls` (`bool`): default `false` (enables `STARTTLS`)
- `dsn` (`bool`): default `false` (adds DSN hints to `RCPT TO`)
- `keepAlive` (`bool`): default `false` (reuses the SMTP connection across sends)

📌 Note: `tls=true` enables `STARTTLS`. This mailer does not automatically secure the connection based on port; on most servers, use port `587` for `STARTTLS`.

📌 Note: The default port is `465`, which is commonly used for *implicit TLS* (SMTPS). This handler only performs implicit TLS when you prefix `host` with `tls://` (or `ssl://`) and leave `tls` as `false`.

#### Security considerations

⚠️ `SmtpMailer` disables TLS certificate verification (`verify_peer` / `verify_peer_name` are `false`). This means TLS protects against passive eavesdropping, but it does **not** protect you from man-in-the-middle attacks on untrusted networks.

Practical mitigation options:

- Prefer sending through a trusted internal relay on the same host/network.
- Use a network-level guarantee (for example a private network, VPN, or a service mesh) between your app and your SMTP server.
- If you need strict certificate validation, implement a custom `Mailer` handler that enables peer verification (or proxy SMTP through a component that performs verification).

### Sendmail

Implemented by `SendmailMailer`. Sends via PHP’s `mail()` function and has no handler-specific options.

### Debug

Implemented by `DebugMailer`. Captures outbound messages in memory for inspection:

- `getSentEmails(): array` returns captured messages as `['headers' => ..., 'body' => ...]` arrays.
- `clear(): void` resets the captured message list.

## Selecting a mailer

Use a mailer key to select which stored config to use. When no key is provided, `MailManager::DEFAULT` (`default`) is used.

```php
use Fyre\Mail\MailManager;

$mailers = app(MailManager::class);

$default = $mailers->use();
$debug = $mailers->use('debug');
```

If you are using contextual injection, you can request a mailer key directly on a parameter:

```php
use Fyre\Core\Attributes\Mail;
use Fyre\Mail\Mailer;

function sendWelcome(#[Mail] Mailer $mailer): void
{
    $mailer->email()
        ->setTo('user@example.com')
        ->setSubject('Welcome')
        ->setBodyText("Hello!\n")
        ->send();
}
```

To request a non-default key, pass it to the attribute:

```php
use Fyre\Core\Attributes\Mail;
use Fyre\Mail\Mailer;

function sendWelcomeDebug(#[Mail('debug')] Mailer $mailer): void
{
    $mailer->email()
        ->setTo('user@example.com')
        ->setSubject('Welcome')
        ->setBodyText("Hello!\n")
        ->send();
}
```

## Building one-off mailers

Use `build()` to construct a mailer directly from options without storing it under a key (and without sharing it).

```php
use Fyre\Mail\Handlers\SmtpMailer;

$mailer = $mailers->build([
    'className' => SmtpMailer::class,
    'host' => '127.0.0.1',
    'port' => 587,
    'tls' => true,
]);
```

## Sending emails

Create a message via `Mailer::email()` and send it via `Email::send()` (or directly via `Mailer::send()`). For a deeper guide to building messages, formats, and attachments, see [Emails](emails.md).

```php
$mailer = $mailers->use();

$mailer->email()
    ->setFrom('no-reply@example.com', 'Example App')
    ->setTo('user@example.com')
    ->setSubject('Welcome')
    ->setBodyText("Thanks for signing up.\n")
    ->send();
```

## Method guide

### `MailManager`

#### **Get a shared mailer** (`use()`)

Returns the shared mailer instance for a config key. If the mailer has not been created yet, it is built from the stored config and cached.

Arguments:
- `$key` (`string`): the mailer key (defaults to `MailManager::DEFAULT`).

```php
$default = $mailers->use();
$debug = $mailers->use('debug');
```

#### **Build a mailer instance** (`build()`)

Builds a new mailer instance from an options array (without storing or sharing it).

Arguments:
- `$options` (`array<string, mixed>`): mailer options including `className`.

```php
use Fyre\Mail\Handlers\DebugMailer;

$mailer = $mailers->build([
    'className' => DebugMailer::class,
]);
```

#### **Read stored configuration** (`getConfig()`)

Returns the stored config array. When called with no key, it returns all stored configs.

Arguments:
- `$key` (`string|null`): the mailer key, or `null` to return all configs.

```php
$all = $mailers->getConfig();
$default = $mailers->getConfig('default');
```

#### **Unregister a mailer key** (`unload()`)

Unloads a mailer instance (if loaded) and removes its stored configuration.

Arguments:
- `$key` (`string`): the mailer key (defaults to `MailManager::DEFAULT`).

```php
$mailers->unload('debug');
```

### `Mailer`

#### **Create a new message** (`email()`)

Creates a new `Email` associated with this mailer.

```php
$email = $mailer->email();
```

#### **Send a message** (`send()`)

Sends an `Email` through the handler implementation.

Arguments:
- `$email` (`Email`): the email to send.

```php
$email = $mailer->email()
    ->setTo('user@example.com')
    ->setSubject('Hello')
    ->setBodyText("Hi!\n");

$mailer->send($email);
```

#### **Read handler configuration** (`getConfig()`)

Returns the handler config array (merged defaults and configured options).

```php
$config = $mailer->getConfig();
```

#### **Get the client hostname** (`getClient()`)

Returns the hostname used by handlers that need a client identifier (such as SMTP).

```php
$client = $mailer->getClient();
```

### `DebugMailer`

#### **Read captured messages** (`getSentEmails()`)

Returns the captured messages stored by `DebugMailer`.

```php
use Fyre\Mail\Handlers\DebugMailer;
$mailer = $mailers->use('debug');

if ($mailer instanceof DebugMailer) {
    $sent = $mailer->getSentEmails();
}
```

#### **Clear captured messages** (`clear()`)

Clears the captured message list stored by `DebugMailer`.

```php
use Fyre\Mail\Handlers\DebugMailer;
$mailer = $mailers->use('debug');

if ($mailer instanceof DebugMailer) {
    $mailer->clear();
}
```

## Troubleshooting

- **SMTP connection failed**: confirm `host`/`port`, and ensure your chosen TLS mode matches your server.
- **STARTTLS doesn’t work**: use `host` without `tls://`, `port` usually `587`, and `tls=true`.
- **Implicit TLS (SMTPS) doesn’t work**: use `host` prefixed with `tls://` (or `ssl://`), `port` usually `465`, and `tls=false`.
- **SMTP authentication failed**: set `auth=true` and ensure `username`/`password` are set to non-empty strings.
- **Testing without sending real email**: use the debug mailer (`DebugMailer`) or the test tooling in [Email Testing](../testing/mail.md).

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- `Mailer::send()` throws a `MailException` if an email has no recipients.
- `MailManager::use()` requires that the selected key has a valid stored config with a `className`.
- `SmtpMailer` only enables `STARTTLS` when `tls` is `true` (it does not automatically secure the connection based on port).
- `SmtpMailer` does not enable implicit TLS unless you prefix `host` with `tls://` (or `ssl://`).
- `SmtpMailer` disables TLS certificate verification (`verify_peer` / `verify_peer_name` are `false`) (see [Security considerations](#security-considerations)).
- When `auth` is enabled for `SmtpMailer`, `username` and `password` must be set to strings.

## Related

- [Config](../core/config.md)
- [Emails](emails.md)
- [Email Testing](../testing/mail.md)

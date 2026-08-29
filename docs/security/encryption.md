# Encryption

Use `Fyre\Security\Encryption\EncryptionManager` when you need to encrypt values before storing them outside the process.

It is designed for edge-storage cases such as cookies, URLs, and external stores where you need confidentiality plus tamper detection.

## Table of Contents

- [Start here](#start-here)
- [Configuring encrypters](#configuring-encrypters)
  - [Base options](#base-options)
  - [Built-in encrypter handlers](#built-in-encrypter-handlers)
  - [Example configuration](#example-configuration)
- [Selecting an encrypter](#selecting-an-encrypter)
- [Building one-off encrypters](#building-one-off-encrypters)
- [Encrypting and decrypting values](#encrypting-and-decrypting-values)
- [Keys](#keys)
  - [Example: generate and store a key (one-time)](#example-generate-and-store-a-key-one-time)
- [Custom encrypters](#custom-encrypters)
- [Method guide](#method-guide)
  - [`EncryptionManager`](#encryptionmanager)
  - [`Encrypter`](#encrypter)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Use encryption when you want to:

- keep stored values confidential outside the process
- detect tampering when encrypted values come back
- choose between named encrypter configs or one-off encrypter instances

An `Encrypter` implements three operations:

- `encrypt(mixed $data, string $key): string` — serialize and encrypt data into an opaque string.
- `decrypt(string $data, string $key): mixed` — verify integrity, decrypt, then unserialize back to the original value.
- `generateKey(int|null $length = null): string` — generate cryptographically random bytes for use as a key

`EncryptionManager` ships with two built-in handler configs: `default` (libsodium) and `openssl`.

Both handlers serialize data before encryption. `SodiumEncrypter` uses the authentication built into `secretbox`, while `OpenSslEncrypter` authenticates its ciphertext with an HMAC.

## Configuring encrypters

`EncryptionManager` reads configuration from the `Encryption` key in [Config](../core/config.md) and merges it with built-in defaults.

Each entry under `Encryption` is a named encrypter definition:

- `className` (required): a class that extends `Fyre\Security\Encryption\Encrypter`
- additional keys: handler options merged into the handler’s defaults

### Base options

- `className` (`class-string<Fyre\Security\Encryption\Encrypter>`): the encrypter class to build.
- `digest` (`string`): the digest algorithm used for HKDF key derivation and, by `OpenSslEncrypter`, HMAC (default: `SHA512`).

### Built-in encrypter handlers

The options below are specific to the built-in handlers under `Fyre\Security\Encryption\Handlers\*`.

#### `SodiumEncrypter`

- `blockSize` (`int`): serialization padding block size (default: `16`).

Prefer `SodiumEncrypter` when libsodium is available and you want a modern, opinionated construction.

Class: `Fyre\Security\Encryption\Handlers\SodiumEncrypter`

#### `OpenSslEncrypter`

- `cipher` (`string`): the OpenSSL cipher to use (default: `AES-256-CTR`).

Use `OpenSslEncrypter` when you need a specific OpenSSL cipher for compatibility.

Class: `Fyre\Security\Encryption\Handlers\OpenSslEncrypter`

### Example configuration

```php
use Fyre\Security\Encryption\Handlers\OpenSslEncrypter;
use Fyre\Security\Encryption\Handlers\SodiumEncrypter;

return [
    'Encryption' => [
        'default' => [
            'className' => SodiumEncrypter::class,
        ],
        'openssl' => [
            'className' => OpenSslEncrypter::class,
            'cipher' => 'AES-256-CTR',
        ],
    ],
];
```

## Selecting an encrypter

Use an encrypter key to select which stored config to use. When no key is provided, `EncryptionManager::DEFAULT` (`default`) is used.

```php
use Fyre\Security\Encryption\EncryptionManager;

$encrypters = app(EncryptionManager::class);

$default = $encrypters->use();
$openssl = $encrypters->use('openssl');
```

`encryption($key)` resolves the configured encrypter directly; see [Helpers](../core/helpers.md).

```php
$encrypter = encryption();
$openssl = encryption('openssl');
```

## Building one-off encrypters

Use `build()` to construct an encrypter directly from options without storing it under a key (and without sharing it).

```php
use Fyre\Security\Encryption\Handlers\OpenSslEncrypter;

$encrypter = $encrypters->build([
    'className' => OpenSslEncrypter::class,
    'cipher' => 'AES-256-CTR',
]);
```

## Encrypting and decrypting values

Encryption returns raw binary strings. If you need to store ciphertext in a text-only channel (cookies, query strings, JSON), encode it (for example with `base64_encode()`).

```php
use Fyre\Security\Encryption\EncryptionManager;

$encrypter = app(EncryptionManager::class)->use();

$encodedKey = getenv('APP_ENCRYPTION_KEY');
$key = is_string($encodedKey) ? base64_decode($encodedKey, true) : false;

if ($key === false || $key === '') {
    throw new RuntimeException('Missing or invalid APP_ENCRYPTION_KEY.');
}

$value = ['userId' => 42, 'roles' => ['admin']];

$ciphertext = $encrypter->encrypt($value, $key);
$encoded = base64_encode($ciphertext);

$decoded = base64_decode($encoded, true);
if ($decoded === false) {
    throw new RuntimeException('Invalid ciphertext encoding.');
}

$restored = $encrypter->decrypt($decoded, $key);
```

## Keys

Keys are provided by the caller, and must be treated as secrets. Generating keys with `Encrypter::generateKey()` avoids weak or predictable input.

Encrypter keys are raw bytes. If you store them in environment variables or config files, encode them (for example with base64) and decode back to bytes before calling `encrypt()` / `decrypt()`.

### Example: generate and store a key (one-time)

```php
use Fyre\Security\Encryption\EncryptionManager;

$encrypter = app(EncryptionManager::class)->use();

$rawKey = $encrypter->generateKey();
$envValue = base64_encode($rawKey);
```

- `SodiumEncrypter::generateKey()` defaults to `SODIUM_CRYPTO_SECRETBOX_KEYBYTES` when no length is supplied.
- `OpenSslEncrypter::generateKey()` defaults to 24 bytes when no length is supplied.

Changing the key means previously encrypted values become undecryptable with the new key.

## Custom encrypters

To add a custom encrypter handler:

- implement a class that extends `Fyre\Security\Encryption\Encrypter`
- implement `decrypt()`, `encrypt()`, and `generateKey()`
- register it in config with a `className` entry under `Encryption`

## Method guide

`$key` must contain raw encryption key bytes (not a base64 string).

If you use contextual injection, `#[Encryption('key')]` can request a configured encrypter while the container is building an object or calling a callable; see [Contextual attributes](../core/contextual-attributes.md).

### `EncryptionManager`

#### **Get a shared encrypter** (`use()`)

Returns the shared encrypter instance for a config key.

Arguments:
- `$key` (`string`): the encrypter key (defaults to `default`).

```php
$default = $encrypters->use();
$openssl = $encrypters->use('openssl');
```

#### **Build an encrypter instance** (`build()`)

Builds a new encrypter instance from an options array (without storing or sharing it).

Arguments:
- `$options` (`array<string, mixed>`): encrypter options including `className`.

```php
use Fyre\Security\Encryption\Handlers\OpenSslEncrypter;

$temp = $encrypters->build([
    'className' => OpenSslEncrypter::class,
    'cipher' => 'AES-256-CTR',
]);
```

#### **Read stored configuration** (`getConfig()`)

Returns the stored config array. When called with no key, it returns all stored configs.

Arguments:
- `$key` (`string|null`): the encrypter key, or `null` to return all configs.

```php
$all = $encrypters->getConfig();
$default = $encrypters->getConfig('default');
```

#### **Add configuration** (`setConfig()`)

Registers a new config entry. This does not overwrite an existing key.

Arguments:
- `$key` (`string`): the config key to add.
- `$options` (`array<string, mixed>`): the encrypter options.

```php
use Fyre\Security\Encryption\Handlers\OpenSslEncrypter;

$encrypters->setConfig('compat', [
    'className' => OpenSslEncrypter::class,
    'cipher' => 'AES-256-CTR',
]);
```

#### **Unload a config and shared instance** (`unload()`)

Removes a config entry and any already-loaded shared instance for that key.

Arguments:
- `$key` (`string`): the encrypter key to unload (defaults to `default`).

```php
$encrypters->unload('compat');
```

### `Encrypter`

#### **Encrypt a value** (`encrypt()`)

Serializes and encrypts a value using a caller-provided key.

Arguments:
- `$data` (`mixed`): the value to encrypt.
- `$key` (`string`): the encryption key bytes.

```php
$ciphertext = $encrypter->encrypt(['userId' => 42], $key);
```

#### **Decrypt a value** (`decrypt()`)

Verifies integrity, decrypts, then unserializes the value back to the original PHP type.

Arguments:
- `$data` (`string`): the encrypted bytes.
- `$key` (`string`): the encryption key bytes.

```php
$ciphertext = $encrypter->encrypt('secret', $key);
$value = $encrypter->decrypt($ciphertext, $key);
```

#### **Generate a random key** (`generateKey()`)

Generates random bytes suitable for use as an encryption key.

Arguments:
- `$length` (`int|null`): the number of bytes to generate (or `null` to use the handler default).

```php
$key = $encrypter->generateKey();
$key16 = $encrypter->generateKey(16);
```

#### **Inspect handler config** (`getConfig()`)

Returns the merged handler configuration (defaults plus options).

```php
$options = $encrypter->getConfig();
```

## Behavior notes

A few behaviors are worth keeping in mind:

- Ciphertext is binary; encode it for transport through text systems and decode back to the original bytes before calling `decrypt()`.
- Encrypted values are serialized before encryption and unserialized after decryption; only decrypt values you previously encrypted, and ensure any object types are available at decrypt time.
- When integrity checks fail, `decrypt()` throws `Fyre\Security\Encryption\Exceptions\EncryptionException`.
- `EncryptionManager::use($key)` requires a valid config entry containing a `className`; requesting a missing key (or invalid class) results in an `InvalidArgumentException` from `build()`.
- `EncryptionManager::setConfig()` throws if the key already exists; to replace a key, call `unload()` first.
- The `digest` option controls HKDF key derivation for both handlers and HMAC behavior for `OpenSslEncrypter`; it must be supported by `hash_hmac()`.

## Related

- [Security](index.md) - security features for requests, responses, and stored values
- [Config](../core/config.md) - configure encrypters in `config/app.php`
- [Helpers](../core/helpers.md) - `encryption($key)` helper
- [Contextual attributes](../core/contextual-attributes.md) - `#[Encryption]`

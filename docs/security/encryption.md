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
- [Managing encrypter configurations](#managing-encrypter-configurations)
- [Encrypting and decrypting values](#encrypting-and-decrypting-values)
- [Keys](#keys)
  - [Example: generate and store a key (one-time)](#example-generate-and-store-a-key-one-time)
- [Custom encrypters](#custom-encrypters)
- [Related](#related)

## Start here

Use encryption when you want to:

- keep stored values confidential outside the process
- detect tampering when encrypted values come back
- choose between named encrypter configs or one-off encrypter instances

An `Encrypter` implements three operations:

- `encrypt(mixed $data, string $key): string` serializes and encrypts data into an opaque string
- `decrypt(string $data, string $key): mixed` verifies integrity, decrypts, and restores the original value
- `generateKey(int|null $length = null): string` generates cryptographically random key bytes

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

The digest must be supported by `hash_hmac()`; an unsupported value raises an `InvalidArgumentException` when the encrypter is built.

### Built-in encrypter handlers

The options below are specific to the built-in handlers under `Fyre\Security\Encryption\Handlers\*`.

#### `SodiumEncrypter`

- `blockSize` (`int`): serialization padding block size (default: `16`).

Prefer `SodiumEncrypter` when libsodium is available and you want a modern, opinionated construction.

`blockSize` must be greater than zero; invalid values raise an `InvalidArgumentException`.

Class: `Fyre\Security\Encryption\Handlers\SodiumEncrypter`

#### `OpenSslEncrypter`

- `cipher` (`string`): the OpenSSL cipher to use (default: `AES-256-CTR`).

Use `OpenSslEncrypter` when you need a specific OpenSSL cipher for compatibility.

The cipher must be supported by OpenSSL and use an initialization vector. Invalid ciphers raise an `InvalidArgumentException`.

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

Requesting an unknown key attempts to build an empty config and raises an `InvalidArgumentException`. If you use contextual injection, `#[Encryption('key')]` resolves a configured encrypter while the container is building an object or calling a callable; see [Contextual attributes](../core/contextual-attributes.md).

## Building one-off encrypters

Use `build()` to construct an encrypter directly from options without storing it under a key (and without sharing it).

```php
use Fyre\Security\Encryption\Handlers\OpenSslEncrypter;

$encrypter = $encrypters->build([
    'className' => OpenSslEncrypter::class,
    'cipher' => 'AES-256-CTR',
]);
```

`build()` requires a valid `className` that extends `Encrypter` and always returns a new, unshared instance.

## Managing encrypter configurations

Configurations can also be managed at runtime:

```php
use Fyre\Security\Encryption\Handlers\OpenSslEncrypter;

$encrypters->setConfig('compat', [
    'className' => OpenSslEncrypter::class,
    'cipher' => 'AES-256-CTR',
]);

$config = $encrypters->getConfig('compat');
$encrypters->unload('compat');
```

`getConfig()` returns all configs when no key is supplied. `setConfig()` does not replace an existing key and raises an `InvalidArgumentException` on duplicates. `unload()` removes both the config and any shared instance already built from it.

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

Both handlers serialize values before encryption and unserialize them after decryption. Only decrypt values produced by your application, and ensure any serialized object classes are available when decrypting. A malformed value, wrong key, or failed integrity check raises `Fyre\Security\Encryption\Exceptions\EncryptionException`.

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

An explicit key length must be greater than zero. Invalid lengths raise an `InvalidArgumentException`.

Changing the key means previously encrypted values become undecryptable with the new key.

## Custom encrypters

To add a custom encrypter handler:

- implement a class that extends `Fyre\Security\Encryption\Encrypter`
- implement `decrypt()`, `encrypt()`, and `generateKey()`
- register it in config with a `className` entry under `Encryption`

## Related

- [Security](index.md) - security features for requests, responses, and stored values
- [Config](../core/config.md) - configure encrypters in `config/app.php`
- [Helpers](../core/helpers.md) - `encryption($key)` helper
- [Contextual attributes](../core/contextual-attributes.md) - `#[Encryption]`

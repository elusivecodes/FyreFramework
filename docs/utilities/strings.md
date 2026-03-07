# Strings

`Str` (`Fyre\Utility\Str`) is a static string utility class for common transformations (casing, identifiers, slicing, search/replace, padding, and escaping).


## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [Constants](#constants)
- [Method guide](#method-guide)
  - [Casing and identifiers](#casing-and-identifiers)
  - [Searching and matching](#searching-and-matching)
  - [Extracting and slicing](#extracting-and-slicing)
  - [Replacing](#replacing)
  - [Trimming and splitting](#trimming-and-splitting)
  - [Prefix, suffix, padding, and repetition](#prefix-suffix-padding-and-repetition)
  - [Encoding, randomness, and utilities](#encoding-randomness-and-utilities)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use `Str` when you want consistent, reusable string operations without pulling in extra dependencies. It is an abstract class with static methods. Most methods are thin wrappers around PHP string functions, with a few convenience helpers for identifier-style casing.

For pluralization/singularization and convention helpers for class/table names, see [Inflection](inflection.md).

Examples on this page assume `Str` refers to `Fyre\Utility\Str`.

## Quick start

```php
$class = Str::pascal('user_profile'); // "UserProfile"
$method = Str::camel('user_profile'); // "userProfile"
$id = Str::kebab('UserProfile');      // "user-profile"

$name = 'users.show';
$group = Str::before($name, '.');       // "users"
$action = Str::afterLast($name, '.');   // "show"

$html = Str::escape('<a href="?q=test">link</a>');
```

## Constants

`Str` exposes a few constants that are commonly useful with its methods:

- Character sets for `random()`:
  - `Str::ALPHA`
  - `Str::NUMERIC`
  - `Str::ALPHANUMERIC`
- HTML escaping flags for `escape()`:
  - `Str::ENT_*` constants mirror PHP’s `ENT_*` constants (for example: `Str::ENT_QUOTES`, `Str::ENT_HTML5`).
- Padding types for `pad()`:
  - `Str::PAD_LEFT`, `Str::PAD_RIGHT`, `Str::PAD_BOTH`
- Default character mask for trimming:
  - `Str::WHITESPACE_MASK`

## Method guide

### Casing and identifiers

#### **Convert to camelCase** (`camel()`)

A convenience wrapper around `pascal()` that lowercases the first character.

Arguments:
- `$string` (`string`): the input string.

```php
$value = Str::camel('user_profile'); // "userProfile"
```

#### **Convert to PascalCase** (`pascal()`)

Converts `-` and `_` to word breaks, uppercases each word, then concatenates.

Arguments:
- `$string` (`string`): the input string.

```php
$value = Str::pascal('user_profile'); // "UserProfile"
```

#### **Convert to snake_case** (`snake()`)

Builds an identifier-style slug using `_` as the delimiter.

Arguments:
- `$string` (`string`): the input string.

```php
$value = Str::snake('UserProfile'); // "user_profile"
```

#### **Convert to kebab-case** (`kebab()`)

Builds an identifier-style slug using `-` as the delimiter.

Arguments:
- `$string` (`string`): the input string.

```php
$value = Str::kebab('UserProfile'); // "user-profile"
```

#### **Build a simple identifier slug** (`slug()`)

Converts word boundaries and Camel/Pascal-case transitions into a delimiter, then lowercases.

Arguments:
- `$string` (`string`): the input string.
- `$delimiter` (`string`): the delimiter to insert between boundaries.

```php
$value = Str::slug('UserProfile');     // "user_profile"
$value = Str::slug('UserProfile', '-'); // "user-profile"
```

#### **Convert to lowercase** (`lower()`)

Lowercases a string.

Arguments:
- `$string` (`string`): the input string.

```php
$value = Str::lower('Hello'); // "hello"
```

#### **Convert to UPPERCASE** (`upper()`)

Uppercases a string.

Arguments:
- `$string` (`string`): the input string.

```php
$value = Str::upper('Hello'); // "HELLO"
```

#### **Capitalize each word** (`title()`)

Lowercases first, then title-cases words using `ucwords()`.

Arguments:
- `$string` (`string`): the input string.

```php
$value = Str::title('hello WORLD'); // "Hello World"
```

#### **Capitalize the first character** (`capitalize()`)

Lowercases first, then uppercases the first character.

Arguments:
- `$string` (`string`): the input string.

```php
$value = Str::capitalize('hELLO'); // "Hello"
```

### Searching and matching

#### **Check for a substring** (`contains()`)

Checks whether a string contains a substring.

Arguments:
- `$string` (`string`): the input string.
- `$search` (`string`): the search string.

```php
$ok = Str::contains('hello world', 'world'); // true
```

#### **Check for all substrings** (`containsAll()`)

Checks whether a string contains all of the given substrings.

Arguments:
- `$string` (`string`): the input string.
- `$searches` (`string[]`): the search strings.

```php
$ok = Str::containsAll('hello world', ['hello', 'world']); // true
```

#### **Check for any substring** (`containsAny()`)

Checks whether a string contains any of the given substrings.

Arguments:
- `$string` (`string`): the input string.
- `$searches` (`string[]`): the search strings.

```php
$ok = Str::containsAny('hello world', ['nope', 'world']); // true
```

#### **Check prefix match** (`startsWith()`)

Checks whether a string begins with a substring.

Arguments:
- `$string` (`string`): the input string.
- `$search` (`string`): the search string.

```php
$ok = Str::startsWith('hello world', 'hello'); // true
```

#### **Check suffix match** (`endsWith()`)

Checks whether a string ends with a substring.

Arguments:
- `$string` (`string`): the input string.
- `$search` (`string`): the search string.

```php
$ok = Str::endsWith('hello world', 'world'); // true
```

#### **Find first index** (`indexOf()`)

Returns the position of the first occurrence of a substring, or `-1` if not found.

Arguments:
- `$string` (`string`): the input string.
- `$search` (`string`): the search string.
- `$start` (`int`): the starting offset.

```php
$pos = Str::indexOf('hello world', 'o'); // 4
```

#### **Find last index** (`lastIndexOf()`)

Returns the position of the last occurrence of a substring, or `-1` if not found.

Arguments:
- `$string` (`string`): the input string.
- `$search` (`string`): the search string.
- `$start` (`int`): the starting offset (passed to `strrpos()`).

```php
$pos = Str::lastIndexOf('hello world', 'o'); // 7
```

### Extracting and slicing

#### **Get substring after first match** (`after()`)

Returns the part of a string after the first occurrence of a substring. If the substring is not found, the original string is returned.

Arguments:
- `$string` (`string`): the input string.
- `$search` (`string`): the search string.

```php
$value = Str::after('users.show', '.'); // "show"
```

#### **Get substring after last match** (`afterLast()`)

Returns the part of a string after the last occurrence of a substring. If the substring is not found, the original string is returned.

Arguments:
- `$string` (`string`): the input string.
- `$search` (`string`): the search string.

```php
$value = Str::afterLast('a.b.c', '.'); // "c"
```

#### **Get substring before first match** (`before()`)

Returns the part of a string before the first occurrence of a substring. If the substring is not found, the original string is returned.

Arguments:
- `$string` (`string`): the input string.
- `$search` (`string`): the search string.

```php
$value = Str::before('users.show', '.'); // "users"
```

#### **Get substring before last match** (`beforeLast()`)

Returns the part of a string before the last occurrence of a substring. If the substring is not found, the original string is returned.

Arguments:
- `$string` (`string`): the input string.
- `$search` (`string`): the search string.

```php
$value = Str::beforeLast('a.b.c', '.'); // "a.b"
```

#### **Get string length (bytes)** (`length()`)

Returns the length of a string (in bytes).

Arguments:
- `$string` (`string`): the input string.

```php
$bytes = Str::length('hello'); // 5
```

#### **Limit to a byte length** (`limit()`)

Limits a string to a specified number of bytes and appends a suffix when limited.

Arguments:
- `$string` (`string`): the input string.
- `$limit` (`int`): the number of bytes to limit at.
- `$append` (`string`): the substring to append if the string is limited.

```php
$value = Str::limit('hello world', 5); // "hello…"
```

#### **Slice a string** (`slice()`)

Returns a portion of a string using `substr()`.

Arguments:
- `$string` (`string`): the input string.
- `$start` (`int`): the starting offset.
- `$length` (`int|null`): the maximum length to return.

```php
$value = Str::slice('hello world', 6, 5); // "world"
```

### Replacing

#### **Replace all occurrences** (`replace()`)

Replaces all occurrences of a substring.

Arguments:
- `$string` (`string`): the input string.
- `$search` (`string`): the substring to replace.
- `$replace` (`string`): the replacement substring.

```php
$value = Str::replace('a-b-c', '-', '_'); // "a_b_c"
```

#### **Replace first occurrence** (`replaceFirst()`)

Replaces the first occurrence of a substring. If the search string is empty, the input is returned unchanged.

Arguments:
- `$string` (`string`): the input string.
- `$search` (`string`): the substring to replace.
- `$replace` (`string`): the replacement substring.

```php
$value = Str::replaceFirst('a-b-c', '-', '_'); // "a_b-c"
```

#### **Replace last occurrence** (`replaceLast()`)

Replaces the last occurrence of a substring. If the search string is empty, the input is returned unchanged.

Arguments:
- `$string` (`string`): the input string.
- `$search` (`string`): the substring to replace.
- `$replace` (`string`): the replacement substring.

```php
$value = Str::replaceLast('a-b-c', '-', '_'); // "a-b_c"
```

#### **Replace multiple pairs** (`replaceEach()`)

Replaces key/value pairs using `str_replace()` over the replacement map’s keys and values.

Arguments:
- `$string` (`string`): the input string.
- `$replacements` (`array<string, string>`): a map of search strings to replacements.

```php
$value = Str::replaceEach('Hello, :name!', [
    ':name' => 'Taylor',
]); // "Hello, Taylor!"
```

#### **Replace sequentially** (`replaceArray()`)

Replaces each occurrence of `$search` with the next value from `$replacements` (or `''` when replacements run out). If the search string is empty, the input is returned unchanged.

Arguments:
- `$string` (`string`): the input string.
- `$search` (`string`): the substring to replace.
- `$replacements` (`string[]`): Replacement strings used in order.

```php
$value = Str::replaceArray('?, ?, ?', '?', ['a', 'b']); // "a, b, "
```

#### **Replace at a position** (`replaceAt()`)

Replaces text within a portion of a string using `substr_replace()`.

Arguments:
- `$string` (`string`): the input string.
- `$replace` (`string`): the replacement substring.
- `$position` (`int`): the position to replace from.
- `$length` (`int`): the length to replace.

```php
$value = Str::replaceAt('hello world', 'Fyre', 6, 5); // "hello Fyre"
```

### Trimming and splitting

#### **Trim both ends** (`trim()`)

Trims whitespace (or other characters) from the start and end of a string.

Arguments:
- `$string` (`string`): the input string.
- `$mask` (`string`): the characters to trim.

```php
$value = Str::trim("  hello \n"); // "hello"
```

#### **Trim the end** (`trimEnd()`)

Trims whitespace (or other characters) from the end of a string.

Arguments:
- `$string` (`string`): the input string.
- `$mask` (`string`): the characters to trim.

```php
$value = Str::trimEnd("hello...\n", ".\n"); // "hello"
```

#### **Trim the start** (`trimStart()`)

Trims whitespace (or other characters) from the start of a string.

Arguments:
- `$string` (`string`): the input string.
- `$mask` (`string`): the characters to trim.

```php
$value = Str::trimStart('---hello', '-'); // "hello"
```

#### **Split by delimiter** (`split()`)

Splits a string by a delimiter.

Arguments:
- `$string` (`string`): the input string.
- `$delimiter` (`string`): the delimiter to split by.
- `$limit` (`int`): the maximum number of parts to return.

```php
$parts = Str::split('a,b,c', ','); // ["a", "b", "c"]
```

#### **Split into fixed chunks** (`chunk()`)

Splits a string into chunks of a maximum size.

Arguments:
- `$string` (`string`): the input string.
- `$size` (`int`): the maximum length of each chunk.

```php
$parts = Str::chunk('abcdef', 2); // ["ab", "cd", "ef"]
```

### Prefix, suffix, padding, and repetition

#### **Ensure a prefix** (`start()`)

Prepends a substring if the string does not already start with it.

Arguments:
- `$string` (`string`): the input string.
- `$search` (`string`): the substring to prepend.

```php
$value = Str::start('path/to/file', '/'); // "/path/to/file"
```

#### **Ensure a suffix** (`end()`)

Appends a substring if the string does not already end with it.

Arguments:
- `$string` (`string`): the input string.
- `$search` (`string`): the substring to append.

```php
$value = Str::end('path/to/dir', '/'); // "path/to/dir/"
```

#### **Pad to a length** (`pad()`)

Pads a string to a specified length.

Arguments:
- `$string` (`string`): the input string.
- `$length` (`int`): the desired length.
- `$padding` (`string`): the padding string.
- `$padType` (`int`): the padding type (`Str::PAD_LEFT`, `Str::PAD_RIGHT`, or `Str::PAD_BOTH`).

```php
$value = Str::pad('5', 3, '0', Str::PAD_LEFT); // "005"
```

#### **Pad the end** (`padEnd()`)

Pads the end of a string to a specified length.

Arguments:
- `$string` (`string`): the input string.
- `$length` (`int`): the desired length.
- `$padding` (`string`): the padding string.

```php
$value = Str::padEnd('hi', 5, '.'); // "hi..."
```

#### **Pad the start** (`padStart()`)

Pads the start of a string to a specified length.

Arguments:
- `$string` (`string`): the input string.
- `$length` (`int`): the desired length.
- `$padding` (`string`): the padding string.

```php
$value = Str::padStart('hi', 5, '.'); // "...hi"
```

#### **Repeat a string** (`repeat()`)

Repeats a string a specified number of times.

Arguments:
- `$string` (`string`): the input string.
- `$count` (`int`): the number of times to repeat.

```php
$value = Str::repeat('na', 4); // "nananana"
```

### Encoding, randomness, and utilities

#### **Escape HTML** (`escape()`)

Escapes characters for use in HTML using `htmlspecialchars()` and UTF-8 encoding.

Arguments:
- `$string` (`string`): the input string.
- `$flags` (`int`): the escaping flags (combine `Str::ENT_*` constants).

```php
$value = Str::escape('<a href="?q=test">link</a>');
```

#### **Transliterate to ASCII** (`transliterate()`)

Attempts to transliterate UTF-8 text to ASCII using `iconv()`.

Arguments:
- `$string` (`string`): the input string.

```php
$value = Str::transliterate('München'); // "Munchen" (platform dependent)
```

#### **Generate a random string** (`random()`)

Generates a random string from a character set.

Arguments:
- `$length` (`int`): the length of the string to generate.
- `$chars` (`string`): the character set to use.

```php
$value = Str::random(8); // e.g. "a8Jk1QpZ"
```

#### **Reverse a string** (`reverse()`)

Reverses the contents of a string.

Arguments:
- `$string` (`string`): the input string.

```php
$value = Str::reverse('abc'); // "cba"
```

#### **Shuffle a string** (`shuffle()`)

Randomly shuffles the characters of a string.

Arguments:
- `$string` (`string`): the input string.

```php
$value = Str::shuffle('abc'); // e.g. "bca"
```

#### **Test for a string** (`isString()`)

Checks whether a value is a string.

Arguments:
- `$value` (`mixed`): the value to test.

```php
$ok = Str::isString('hello'); // true
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `chunk()`, `length()`, `limit()`, `slice()`, and `replaceAt()` operate on bytes (via `str_split()`, `strlen()`, `substr()`, and `substr_replace()`), which can split multi-byte UTF-8 characters.
- `after()`, `afterLast()`, `before()`, and `beforeLast()` return the original string when the search string is empty or not found.
- `startsWith()` and `endsWith()` delegate to PHP’s `str_starts_with()` / `str_ends_with()`, so an empty search string returns `true`.
- `split()` returns an empty array when the delimiter is an empty string.
- `replaceFirst()`, `replaceLast()`, and `replaceArray()` return the original string when the search string is empty.
- `replaceArray()` replaces each match with the next replacement, and uses `''` once replacements run out.
- `slug()`, `snake()`, and `kebab()` are designed for identifier-style slugs: they do not perform full URL-safe normalization, and they do not normalize existing separators.
- `transliterate()` temporarily sets `LC_CTYPE` to `en_US.UTF8` and uses `iconv()` with `TRANSLIT//IGNORE`; output depends on the installed locales and iconv implementation.
- `random()` uses `random_int()` and throws `InvalidArgumentException` when `$length < 0` or `$chars === ''`.

## Related

- [Utilities](index.md)
- [Inflection](inflection.md)

# PDF

Use `Pdf` when you want to generate PDFs by rendering HTML through headless Chrome or Chromium.

You can render a URL, a file path, or an in-memory HTML string.

## Table of Contents

- [Start here](#start-here)
- [Environment checklist](#environment-checklist)
- [Configuration](#configuration)
  - [Chrome/Chromium binary](#chromechromium-binary)
  - [Timeout](#timeout)
- [Method guide](#method-guide)
  - [Creating a PDF](#creating-a-pdf)
  - [Output](#output)
  - [Option accessors](#option-accessors)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

This is a good fit for invoices, receipts, reports, and exports where you already have HTML output.

`Pdf` shells out to a Chrome or Chromium binary with `--headless` and `--print-to-pdf`.

```php
use Fyre\Utility\Pdf;

Pdf::createFromUrl('https://example.com/invoice/123')
    ->save('tmp/invoice-123.pdf');
```

```php
use Fyre\Utility\Pdf;

$html = '<h1>Invoice</h1><p>Thanks!</p>';

$bytes = Pdf::createFromHtml($html)->toBinary();
```

## Environment checklist

Before debugging API usage, verify these prerequisites:

- A Chrome/Chromium binary is installed and executable (see [Configuration → Chrome/Chromium binary](#chromechromium-binary)).
- The output directory is writable by the current PHP process.
- When rendering HTML, use absolute URLs for assets (or set a `<base>` tag) so CSS/images/fonts can be loaded.

## Configuration

Examples below assume `Pdf` refers to `Fyre\Utility\Pdf`.

### Chrome/Chromium binary

The `binaryPath` option controls the Chrome/Chromium executable used by a `Pdf` instance. It defaults to `google-chrome`. Override it when your environment uses a different binary name or a full path:

```php
$pdf = Pdf::createFromUrl('https://example.com/invoice/123', [
    'binaryPath' => '/usr/bin/chromium',
]);
```

### Timeout

The `timeout` option is expressed in milliseconds and is passed to Chrome/Chromium via `--timeout`. It defaults to `5000`.

```php
$pdf = Pdf::createFromUrl('https://example.com/invoice/123', [
    'timeout' => 10000,
]);
```

Options apply only to the `Pdf` instance being created. You can combine them when needed:

```php
$pdf = Pdf::createFromUrl('https://example.com/invoice/123', [
    'binaryPath' => '/usr/bin/chromium',
    'timeout' => 10000,
]);
```

## Method guide

### Creating a PDF

#### **Create from an HTML string** (`createFromHtml()`)

Create a `Pdf` instance from an in-memory HTML string. The HTML is embedded into a base64-encoded `data:` URL.

Arguments:

- `$html` (`string`): the HTML string to render.
- `$options` (`array`, optional): the instance options. Supports `binaryPath` (`string`) and `timeout` (`int`).

```php
$pdf = Pdf::createFromHtml(
    '<h1>Report</h1><p>Generated at runtime.</p>',
    ['timeout' => 10000]
);
```

#### **Create from a URL or file path** (`createFromUrl()`)

Create a `Pdf` instance from a URL or file path. The value is passed to Chrome/Chromium as the page to load.

Arguments:

- `$url` (`string`): the URL or file path to render.
- `$options` (`array`, optional): the instance options. Supports `binaryPath` (`string`) and `timeout` (`int`).

```php
$pdf = Pdf::createFromUrl('https://example.com/invoice/123');
```

### Output

#### **Save a PDF to disk** (`save()`)

Generate the PDF and write it to a new file path.

Arguments:

- `$filePath` (`string`): the output file path.

```php
Pdf::createFromUrl('https://example.com/invoice/123')
    ->save('tmp/invoice-123.pdf');
```

#### **Get PDF bytes** (`toBinary()`)

Generate the PDF to a temporary file and return its contents as a string.

```php
$bytes = Pdf::createFromHtml('<h1>Hello</h1>')
    ->toBinary();
```

### Option accessors

#### **Get the binary path** (`getBinaryPath()`)

Get the Chrome/Chromium binary path configured for a `Pdf` instance.

```php
$pdf = Pdf::createFromUrl('https://example.com/invoice/123');
$binaryPath = $pdf->getBinaryPath();
```

#### **Get the timeout** (`getTimeout()`)

Get the timeout configured for a `Pdf` instance, in milliseconds.

```php
$pdf = Pdf::createFromUrl('https://example.com/invoice/123');
$timeout = $pdf->getTimeout();
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `save()` throws an `InvalidArgumentException` when `$filePath` is empty.
- Creating a `Pdf` throws an `InvalidArgumentException` when `timeout` is negative.
- `save()` throws a `RuntimeException` if the file already exists or if PDF generation did not produce the output file.
- `toBinary()` creates a temporary file path and then calls `save()` internally; if temporary file creation, generation, or reading fails, it throws a `RuntimeException`.
- When generating from HTML via `createFromHtml()`, the page source is a `data:` URL. If your HTML uses relative URLs for assets (CSS/images/fonts), use absolute URLs or add a `<base>` tag.
- The generated output uses Chrome/Chromium flags including `--deterministic-mode` and `--no-pdf-header-footer`.

## Related

- [Utilities](index.md)
- [Paths](paths.md)
- [File System](file-system.md)

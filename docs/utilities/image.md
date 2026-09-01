# Images

Use `Image` when you want common GD-based image manipulation without managing `GdImage` resources directly.

It can load images, normalize EXIF orientation, resize or crop them, apply basic filters, and encode the result for files or HTTP output.

## Table of Contents

- [Start here](#start-here)
- [Environment checklist](#environment-checklist)
- [Working with image instances](#working-with-image-instances)
- [Creating images](#creating-images)
- [Resizing and cropping](#resizing-and-cropping)
- [Transforms and filters](#transforms-and-filters)
- [Color analysis](#color-analysis)
- [Output](#output)
- [Output formats](#output-formats)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Load a file, normalize its EXIF orientation, crop it to the required aspect ratio, and save the result:

```php
use Fyre\Utility\Image;

$image = Image::createFromFile('uploads/photo.jpg');

$image->orient()
    ->cover(1200, 630)
    ->save('tmp/social-card.jpg');
```

You can also work with image bytes already held in memory:

```php
$image = Image::createFromString($bytes);
$thumbnail = $image->fit(320, 320)->toBinary('webp');
```

## Environment checklist

Before using `Image`, verify the relevant PHP extensions and GD features:

- The GD extension (`ext-gd`) is required for all image operations.
- The EXIF extension (`ext-exif`) is optional, but is required for `createFromFile()` to detect EXIF orientation.
- Available input formats depend on the formats supported by the installed GD build.
- AVIF, GIF, JPEG, PNG, and WebP output each require the corresponding GD encoder function.
- The directory passed to `save()` must already exist and be writable by the current PHP process.

## Working with image instances

Image transformations are mutable. Methods such as `resize()`, `crop()`, `rotate()`, and `grayscale()` update the current image and return the same `Image` instance, which allows fluent chains.

```php
$image = Image::createFromFile('uploads/photo.jpg');
$result = $image->fit(800, 800)->sharpen();

// $result and $image are the same instance.
```

Create a separate `Image` instance from the original file or bytes when you need to preserve an unmodified version.

## Creating images

| Method | Source |
| --- | --- |
| `Image::createFromFile($filePath)` | an image file |
| `Image::createFromString($data)` | encoded image bytes |

`createFromFile()` records EXIF orientation when the extension is available, but does not apply it until you call `orient()`. Images created from bytes do not retain EXIF orientation.

An unreadable file raises a `RuntimeException`. Data that GD cannot decode raises an `InvalidArgumentException`.

## Resizing and cropping

Use `getWidth()` and `getHeight()` to read the current dimensions in pixels.

| Method | Result |
| --- | --- |
| `resize($width, $height)` | exact dimensions; may change the aspect ratio |
| `fit($width, $height)` | proportional image within the given bounds |
| `contain($width, $height, $background = 'transparent')` | proportional image centered on an exact-size canvas |
| `cover($width, $height)` | proportional image filling the dimensions, cropped from the center |
| `crop($x, $y, $width, $height)` | the selected rectangle |

`fit()`, `contain()`, and `cover()` can upscale the source. A crop must remain within the current image bounds. Backgrounds accept a `Color` or a string understood by the [Colors](colors.md) utility and default to transparent.

## Transforms and filters

| Method | Effect |
| --- | --- |
| `orient()` | apply the EXIF orientation recorded from a file |
| `rotate($degrees, $background = 'transparent')` | rotate clockwise, filling exposed areas with a color |
| `flipHorizontal()` | mirror across the vertical axis |
| `flipVertical()` | mirror across the horizontal axis |
| `blur($passes = 1)` | apply Gaussian blur one or more times |
| `brightness($amount)` | adjust brightness from `-255` through `255` |
| `contrast($amount)` | adjust contrast from `-100` through `100` |
| `grayscale()` | remove color |
| `pixelate($size)` | apply square pixel blocks |
| `sharpen()` | apply GD's mean-removal filter |

`orient()` resets the stored orientation after applying it, so calling it again does not repeat the transformation. Rotation degrees are normalized to one full turn; the value must be finite. Blur passes and pixel size must be greater than zero.

## Color analysis

`dominantColor()` samples the image down to one pixel and returns a six-digit hexadecimal color such as `#0f172a`. It is a quick overall estimate, not a histogram of the most frequent source pixel.

## Output

| Method | Result |
| --- | --- |
| `save($filePath, $quality = 90, $overwrite = false)` | encode using the file extension and write to disk |
| `toBinary($format = 'png', $quality = 90)` | encoded image bytes |
| `toBase64($format = 'png', $quality = 90)` | Base64 without a data URI prefix |
| `toDataUri($format = 'png', $quality = 90)` | a complete Base64 data URI |

Quality defaults to `90` and must be between `0` and `100`. `save()` does not replace an existing file unless `$overwrite` is `true`.

```php
$image->save('tmp/photo.webp', quality: 85);
$src = $image->toDataUri('webp', 85);
```

## Output formats

The following output format names are supported:

- `avif`
- `gif`
- `jpeg` or `jpg`
- `png`
- `webp`

Format names are case-insensitive, and `jpg` is normalized to `jpeg`. The requested encoder must be available in the installed GD build.

JPEG does not support transparency, so JPEG output is flattened onto a white background. PNG, WebP, and AVIF can retain transparency when supported by GD. GIF encoding does not use the quality argument; PNG maps it to a compression level while remaining lossless.

## Behavior notes

- Widths and heights passed to resize and crop methods must be greater than `0`.
- Without the EXIF extension, file images use the default orientation and `orient()` has no effect.
- `save()` chooses the format from the file extension, while in-memory output methods use their `$format` argument.
- Unsupported formats, unavailable encoders, invalid quality values, and invalid geometry raise exceptions rather than returning `false`.

## Related

- [Utilities](index.md)
- [Colors](colors.md)
- [File System](file-system.md)
- [Paths](paths.md)

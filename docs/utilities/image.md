# Images

Use `Image` when you want common GD-based image manipulation without managing `GdImage` resources directly.

It can load images, normalize EXIF orientation, resize or crop them, apply basic filters, and encode the result for files or HTTP output.

## Table of Contents

- [Start here](#start-here)
- [Environment checklist](#environment-checklist)
- [Working with image instances](#working-with-image-instances)
- [Method guide](#method-guide)
  - [Creating images](#creating-images)
  - [Dimensions](#dimensions)
  - [Resizing and cropping](#resizing-and-cropping)
  - [Orientation and transforms](#orientation-and-transforms)
  - [Filters](#filters)
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

## Method guide

Examples below assume `Image` refers to `Fyre\Utility\Image`.

### Creating images

#### **Create from a file** (`createFromFile()`)

Reads an image file and creates an `Image` instance. EXIF orientation is recorded when the EXIF extension is available, but is not applied until you call `orient()`.

Arguments:

- `$filePath` (`string`): the image file path.

```php
$image = Image::createFromFile('uploads/photo.jpg');
```

The method throws a `RuntimeException` when the file cannot be read and an `InvalidArgumentException` when its contents are not valid image data.

#### **Create from image bytes** (`createFromString()`)

Creates an `Image` instance from encoded image data.

Arguments:

- `$data` (`string`): the encoded image data.

```php
$bytes = file_get_contents('uploads/photo.png');
$image = Image::createFromString($bytes);
```

The method throws an `InvalidArgumentException` when GD cannot decode the data. Because there is no source file, EXIF orientation is not retained.

### Dimensions

#### **Get the width** (`getWidth()`)

Returns the current image width in pixels.

```php
$width = $image->getWidth();
```

#### **Get the height** (`getHeight()`)

Returns the current image height in pixels.

```php
$height = $image->getHeight();
```

### Resizing and cropping

#### **Resize to exact dimensions** (`resize()`)

Resizes the image to an exact width and height. The aspect ratio is not preserved automatically.

Arguments:

- `$width` (`int`): the target width in pixels.
- `$height` (`int`): the target height in pixels.

```php
$image->resize(640, 480);
```

#### **Fit within dimensions** (`fit()`)

Resizes the image proportionally so that it fits within the specified maximum width and height.

Arguments:

- `$width` (`int`): the maximum width in pixels.
- `$height` (`int`): the maximum height in pixels.

```php
$image->fit(800, 800);
```

The resulting image may be smaller than one target dimension. The method can also upscale images when the target bounds are larger than the source.

#### **Contain within dimensions** (`contain()`)

Fits the image proportionally, centers it on a canvas with the exact target dimensions, and fills any remaining area with a background color.

Arguments:

- `$width` (`int`): the target canvas width in pixels.
- `$height` (`int`): the target canvas height in pixels.
- `$background` (`Color|string`): the background color. Defaults to `transparent`.

```php
$image->contain(1200, 630, '#0f172a');
```

Color strings use the same parsing rules as the [Colors](colors.md) utility.

#### **Cover dimensions** (`cover()`)

Resizes the image proportionally until it covers the target dimensions, then crops the excess from the center.

Arguments:

- `$width` (`int`): the target width in pixels.
- `$height` (`int`): the target height in pixels.

```php
$image->cover(1200, 630);
```

#### **Crop a rectangle** (`crop()`)

Crops a rectangle from the current image.

Arguments:

- `$x` (`int`): the horizontal offset from the left edge.
- `$y` (`int`): the vertical offset from the top edge.
- `$width` (`int`): the crop width in pixels.
- `$height` (`int`): the crop height in pixels.

```php
$image->crop(100, 50, 400, 300);
```

The crop rectangle must remain entirely within the current image bounds.

### Orientation and transforms

#### **Normalize EXIF orientation** (`orient()`)

Applies the orientation detected by `createFromFile()` using rotations and flips.

```php
$image = Image::createFromFile('uploads/camera-photo.jpg');
$image->orient();
```

After normalization, the stored orientation is reset. Calling `orient()` again does not apply the transformation twice.

#### **Rotate clockwise** (`rotate()`)

Rotates the image clockwise and fills exposed areas with a background color.

Arguments:

- `$degrees` (`float`): the clockwise rotation in degrees. The value must be finite.
- `$background` (`Color|string`): the background color. Defaults to `transparent`.

```php
$image->rotate(45, '#fff');
```

Degrees are normalized to a full rotation, so rotating by `0` or a multiple of `360` leaves the image unchanged.

#### **Flip horizontally** (`flipHorizontal()`)

Mirrors the image across its vertical axis.

```php
$image->flipHorizontal();
```

#### **Flip vertically** (`flipVertical()`)

Mirrors the image across its horizontal axis.

```php
$image->flipVertical();
```

### Filters

#### **Blur the image** (`blur()`)

Applies one or more Gaussian blur passes.

Arguments:

- `$passes` (`int`): the number of passes. Defaults to `1` and must be greater than `0`.

```php
$image->blur(2);
```

#### **Adjust brightness** (`brightness()`)

Adjusts image brightness.

Arguments:

- `$amount` (`int`): the brightness adjustment from `-255` to `255`.

```php
$image->brightness(20);
```

#### **Adjust contrast** (`contrast()`)

Adjusts image contrast.

Arguments:

- `$amount` (`int`): the contrast adjustment from `-100` to `100`.

```php
$image->contrast(15);
```

#### **Convert to grayscale** (`grayscale()`)

Removes color from the image while retaining its dimensions.

```php
$image->grayscale();
```

#### **Pixelate the image** (`pixelate()`)

Applies a pixelation filter using square blocks.

Arguments:

- `$size` (`int`): the pixel block size. It must be greater than `0`.

```php
$image->pixelate(8);
```

#### **Sharpen the image** (`sharpen()`)

Applies GD's mean-removal filter to sharpen the image.

```php
$image->sharpen();
```

### Color analysis

#### **Get the dominant color** (`dominantColor()`)

Samples the image down to one pixel and returns the resulting color as a six-digit hexadecimal string.

```php
$color = $image->dominantColor(); // For example: "#0f172a"
```

This is a fast overall color estimate rather than a histogram of the most frequent source pixel.

### Output

#### **Save to a file** (`save()`)

Encodes the image using the output file extension and writes it to disk.

Arguments:

- `$filePath` (`string`): the output file path, including a supported extension.
- `$quality` (`int`): the output quality from `0` to `100`. Defaults to `90`.
- `$overwrite` (`bool`): whether an existing file may be replaced. Defaults to `false`.

```php
$image->save('tmp/photo.webp', quality: 85);
$image->save('tmp/photo.jpg', overwrite: true);
```

#### **Get encoded bytes** (`toBinary()`)

Encodes the current image and returns its binary contents.

Arguments:

- `$format` (`string`): the output format. Defaults to `png`.
- `$quality` (`int`): the output quality from `0` to `100`. Defaults to `90`.

```php
$bytes = $image->toBinary('webp', 85);
```

#### **Get Base64 data** (`toBase64()`)

Returns the encoded image as a Base64 string without a data URI prefix.

Arguments:

- `$format` (`string`): the output format. Defaults to `png`.
- `$quality` (`int`): the output quality from `0` to `100`. Defaults to `90`.

```php
$base64 = $image->toBase64('png');
```

#### **Get a data URI** (`toDataUri()`)

Returns the encoded image with its media type and Base64 data URI prefix.

Arguments:

- `$format` (`string`): the output format. Defaults to `png`.
- `$quality` (`int`): the output quality from `0` to `100`. Defaults to `90`.

```php
$src = $image->toDataUri('webp', 85);

echo '<img src="'.htmlspecialchars($src).'" alt="Preview">';
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

A few behaviors are worth keeping in mind:

- Transformations mutate the current `Image` and return the same instance.
- Widths and heights passed to resize and crop methods must be greater than `0`.
- `fit()`, `contain()`, and `cover()` may upscale the source image.
- `createFromFile()` records EXIF orientation but does not apply it automatically; call `orient()` explicitly.
- Without the EXIF extension, file images use the default orientation and `orient()` has no effect.
- `save()` does not overwrite an existing file unless `$overwrite` is `true`.
- `save()` chooses the format from the file extension, while in-memory output methods use their `$format` argument.
- Unsupported formats, unavailable encoders, invalid quality values, and invalid geometry raise exceptions rather than returning `false`.
- `dominantColor()` returns a one-pixel average-like sample, not the statistically most common pixel color.

## Related

- [Utilities](index.md)
- [Colors](colors.md)
- [File System](file-system.md)
- [Paths](paths.md)

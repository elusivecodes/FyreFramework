# Images

`Fyre\Utility\Image` loads encoded images into GD, applies mutable transformations, and writes or returns AVIF, GIF, JPEG, PNG, or WebP output.

## Table of Contents

- [Load and transform an image](#load-and-transform-an-image)
- [Environment requirements](#environment-requirements)
- [Method guide](#method-guide)
  - [Creation and dimensions](#creation-and-dimensions)
  - [Resize and crop](#resize-and-crop)
  - [Orientation, transforms, and filters](#orientation-transforms-and-filters)
  - [Analysis and output](#analysis-and-output)
- [Mutation and format behavior](#mutation-and-format-behavior)
- [Related](#related)

## Load and transform an image

Load a file, apply its recorded EXIF orientation, crop it to an output aspect ratio, and save it:

```php
use Fyre\Utility\Image;

$image = Image::createFromFile('uploads/photo.jpg');

$image
    ->orient()
    ->cover(1200, 630)
    ->save('tmp/social-card.jpg');
```

Transformations update `$image` and return the same instance. Load the source again or create another instance from the original bytes when an unchanged copy is required.

## Environment requirements

- `ext-gd` is required for every operation and must support each input or output format being used.
- `ext-exif` is optional. Without it, `createFromFile()` cannot record EXIF orientation and `orient()` has no effect.
- `save()` requires an existing writable parent directory; it does not create folders.
- `ext-gd` and `ext-exif` are Composer suggestions rather than required package dependencies.

## Method guide

The methods below use the `$image` loaded above.

### Creation and dimensions

| Method | Behavior |
| --- | --- |
| `Image::createFromFile(string $filePath): static` | read and decode a file, recording a valid EXIF orientation when available |
| `Image::createFromString(string $data): static` | decode image bytes without retaining EXIF orientation |
| `getWidth(): int` | current pixel width |
| `getHeight(): int` | current pixel height |

Unreadable files throw a `RuntimeException`. Data that GD cannot decode throws an `InvalidArgumentException`. Loaded palette images are converted to true color and configured to retain alpha.

### Resize and crop

| Method | Result |
| --- | --- |
| `resize(int $width, int $height): static` | exact dimensions, regardless of aspect ratio |
| `fit(int $width, int $height): static` | proportional image inside the bounds |
| `contain(int $width, int $height, Color\|string $background = 'transparent'): static` | fitted image centered on an exact-size canvas |
| `cover(int $width, int $height): static` | proportional image filling the bounds, center-cropped |
| `crop(int $x, int $y, int $width, int $height): static` | selected rectangle |

Every dimension must be positive. `crop()` also requires a non-negative origin and a rectangle contained by the current image. `fit()`, `contain()`, and `cover()` can upscale.

Backgrounds accept a [Color](colors.md) or any string supported by `Color::createFromString()`.

### Orientation, transforms, and filters

| Method | Effect |
| --- | --- |
| `orient(): static` | apply recorded EXIF orientation once, then reset it |
| `rotate(float $degrees, Color\|string $background = 'transparent'): static` | rotate clockwise after normalizing to one turn |
| `flipHorizontal(): static` | mirror across the vertical axis |
| `flipVertical(): static` | mirror across the horizontal axis |
| `blur(int $passes = 1): static` | apply Gaussian blur at least once |
| `brightness(int $amount): static` | GD brightness adjustment in `-255..255` |
| `contrast(int $amount): static` | contrast adjustment in `-100..100` |
| `grayscale(): static` | remove color |
| `pixelate(int $size): static` | use positive square pixel blocks |
| `sharpen(): static` | apply GD's mean-removal filter |

Rotation must be finite. A normalized rotation of zero leaves the image unchanged.

### Analysis and output

| Method | Result |
| --- | --- |
| `dominantColor(): string` | six-digit hex estimate obtained by resampling to one pixel |
| `save(string $filePath, int $quality = 90, bool $overwrite = false): void` | encode using the destination extension and write to disk |
| `toBinary(string $format = 'png', int $quality = 90): string` | encoded bytes |
| `toBase64(string $format = 'png', int $quality = 90): string` | Base64 without a URI prefix |
| `toDataUri(string $format = 'png', int $quality = 90): string` | complete `data:image/...;base64,...` URI |

`dominantColor()` is an overall average-like sample, not a histogram of the most frequent source pixel.

Quality must be in `0..100`. `save()` requires a non-empty extension and refuses to replace an existing file unless `$overwrite` is `true`.

## Mutation and format behavior

- Supported output names are `avif`, `gif`, `jpeg`, `jpg`, `png`, and `webp`; matching is case-insensitive and `jpg` normalizes to `jpeg`.
- The corresponding GD encoder function must exist. Unsupported formats and unavailable encoders throw rather than returning `false`.
- JPEG output is composited over white because JPEG has no alpha channel.
- GIF ignores quality. PNG maps quality inversely to compression level while remaining lossless. AVIF, JPEG, and WebP pass quality to GD.
- `save()` chooses the format from the filename; in-memory methods use their `$format` argument.
- Transformations mutate the current `GdImage`; analysis and output methods do not replace it.
- `Image` supports instance and static macros.

## Related

- [Utilities](index.md)
- [Colors](colors.md)
- [File System](file-system.md)
- [Paths](paths.md)

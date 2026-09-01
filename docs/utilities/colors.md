# Colors

`Fyre\Utility\Color\Color` parses CSS colors, converts between color spaces, fits colors to a target gamut, and calculates compositing, luminance, and contrast.

Each value is an immutable concrete color such as `Srgb`, `Hsl`, `Lab`, or `XyzD65`.

## Table of Contents

- [Parse and convert a color](#parse-and-convert-a-color)
- [Supported color spaces](#supported-color-spaces)
- [Method guide](#method-guide)
  - [Parsing](#parsing)
  - [Channel factories](#channel-factories)
  - [Conversion and gamut](#conversion-and-gamut)
  - [Formatting](#formatting)
  - [Alpha and analysis](#alpha-and-analysis)
  - [Channel accessors](#channel-accessors)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Parse and convert a color

Import the base class for format-preserving parsing and a concrete class when the result should be normalized immediately:

```php
use Fyre\Utility\Color\Color;
use Fyre\Utility\Color\Colors\Srgb;

$color = Color::createFromString('hsl(210 90% 55% / 70%)');
$srgb = Srgb::createFromString('color(display-p3 1 0.2 0.2)');
$css = $srgb
    ->fitGamut('srgb')
    ->toString();
```

Calling `Color::createFromString()` returns the concrete space represented by the input. Calling the inherited method on `Srgb` or another concrete class converts the parsed value to that class.

## Supported color spaces

| Family | Classes and `to()` identifiers |
| --- | --- |
| display encodings | `Hex` (`hex`), `Rgb` (`rgb`) |
| RGB profiles | `Srgb` (`srgb`), `SrgbLinear` (`srgb-linear`), `DisplayP3` (`display-p3`), `DisplayP3Linear` (`display-p3-linear`), `A98Rgb` (`a98-rgb`), `ProPhotoRgb` (`prophoto-rgb`), `Rec2020` (`rec2020`) |
| cylindrical | `Hsl` (`hsl`), `Hwb` (`hwb`) |
| perceptual | `Lab` (`lab`), `Lch` (`lch`), `OkLab` (`oklab`), `OkLch` (`oklch`) |
| reference | `XyzD50` (`xyz-d50`), `XyzD65` (`xyz-d65`) |

Concrete classes live under `Fyre\Utility\Color\Colors`.

`Color::CSS_COLORS` maps lowercase CSS names to hex strings. Parsing and `label()` use this table; `Rgb` and `Hex` can also use it when name output is enabled.

## Method guide

The methods below use the imports and color instances established above.

### Parsing

#### **Parse a CSS color** (`createFromString()`)

```php
Color::createFromString(string $string): static
```

Supported input includes:

- CSS named colors and `transparent`
- `#rgb`, `#rgba`, `#rrggbb`, and `#rrggbbaa`
- `rgb()`, `rgba()`, `hsl()`, `hsla()`, `hwb()`, `lab()`, `lch()`, `oklab()`, and `oklch()`
- `color()` with `srgb`, `srgb-linear`, `display-p3`, `display-p3-linear`, `a98-rgb`, `prophoto-rgb`, `rec2020`, `xyz-d50`, `xyz`, or `xyz-d65`

Parsing is case-insensitive and normalizes whitespace. Functions require three channels and accept one optional alpha. Modern space-separated notation uses `/` before alpha; legacy comma notation is accepted for RGB, HSL, and HWB, but separators cannot be mixed.

Numbers may use signs, decimals, percentages where supported, and scientific notation. Angles accept degrees, `%`, `grad`, `rad`, and `turn`. Invalid syntax throws an `InvalidArgumentException`.

### Channel factories

Every factory is inherited by each concrete class. The named source space determines how the channel values are interpreted; the class used for the call determines the returned space:

```php
use Fyre\Utility\Color\Colors\Lab;

$srgb = Srgb::createFromRgb(14, 165, 233);
$lab = Lab::createFromRgb(14, 165, 233);
```

| Source family | Factories |
| --- | --- |
| 0–255 RGB | `createFromRgb($red = 0, $green = 0, $blue = 0, $alpha = 1)` |
| RGB profiles | `createFromSrgb()`, `createFromSrgbLinear()`, `createFromDisplayP3()`, `createFromDisplayP3Linear()`, `createFromA98Rgb()`, `createFromProPhotoRgb()`, `createFromRec2020()` |
| cylindrical | `createFromHsl()`, `createFromHwb()` |
| perceptual | `createFromLab()`, `createFromLch()`, `createFromOkLab()`, `createFromOkLch()` |
| reference | `createFromXyzD50()`, `createFromXyzD65()` |

Every method accepts three `float` channels followed by `float $alpha = 1`; each channel also defaults to `0`. Hex input has no channel factory—parse a hex string or call `toHex()`.

Concrete classes can also be constructed directly with their native channels:

| Spaces | Constructor channels |
| --- | --- |
| `Rgb`, `Hex`, and RGB profiles | red, green, blue, alpha |
| `Hsl` | hue, saturation, lightness, alpha |
| `Hwb` | hue, whiteness, blackness, alpha |
| `Lab`, `OkLab` | lightness, a, b, alpha |
| `Lch`, `OkLch` | lightness, chroma, hue, alpha |
| `XyzD50`, `XyzD65` | x, y, z, alpha |

All constructor arguments default to `0`, except alpha, which defaults to `1`:

```php
$srgb = new Srgb(0.1, 0.6, 0.9, 0.8);
```

### Conversion and gamut

| Method | Behavior |
| --- | --- |
| `space(): string` | current `to()` identifier |
| `to(string $space): Color` | convert to a supported named space; an empty or current identifier returns the same instance |
| `fitGamut(string $space = 'srgb'): static` | reduce OKLCH chroma until the color fits the target gamut, then return it in the original concrete space |

Convenience conversions cover every supported target:

```text
toA98Rgb(), toDisplayP3(), toDisplayP3Linear(), toHex(), toHsl(), toHwb(),
toLab(), toLch(), toOkLab(), toOkLch(), toProPhotoRgb(), toRec2020(),
toRgb(), toSrgb(), toSrgbLinear(), toXyzD50(), toXyzD65()
```

`fitGamut()` supports `a98-rgb`, `display-p3`, `display-p3-linear`, `prophoto-rgb`, `rec2020`, `rgb`, `srgb`, and `srgb-linear`. It returns the current instance when the converted color is already in gamut. Lightness outside the OKLCH range is clamped to black or white with zero chroma.

### Formatting

#### **Format as CSS** (`toString()`)

```php
toString(bool|null $alpha = null, int $precision = 2): string
```

`null` includes alpha only when it is below `1`. Output follows the concrete space:

| Class | Output |
| --- | --- |
| `Hex` | hexadecimal, optionally shortened |
| `Rgb` | `rgb()` |
| `Hsl`, `Hwb`, `Lab`, `Lch`, `OkLab`, `OkLch` | matching CSS function |
| other profiles and reference spaces | `color(<space> ...)` |

`Rgb::toString()` adds `bool $name = false`. `Hex::toString()` adds `bool $shortenHex = true` and `bool $name = false`. Name output emits a CSS name, including `transparent`, when possible.

Casting a color to `string` calls `toString()`.

### Alpha and analysis

| Method | Behavior |
| --- | --- |
| `getAlpha(): float` | current alpha in `0..1` |
| `withAlpha(float $alpha): static` | clone with a clamped alpha |
| `composite(Color $background): static` | source-over composite in sRGB, returned as the foreground's concrete class |
| `luma(): float` | relative luminance calculated through sRGB |
| `contrast(Color $other): float` | WCAG-style luminance contrast ratio |
| `label(): string` | nearest `CSS_COLORS` name by channel distance in the current space |

`contrast()` requires both colors to be fully opaque and otherwise throws a `LogicException`. Convert before `label()` when the comparison should occur in a specific space:

```php
$name = $color->toSrgb()->label();
$ratio = Color::createFromString('#0f172a')
    ->contrast(Color::createFromString('white'));
```

### Channel accessors

Every concrete color exposes public readonly channel and `alpha` properties, plus `toArray()` with the same named channels. Getter methods and immutable `with*()` methods follow the space:

| Spaces | Getters and cloning methods |
| --- | --- |
| RGB-like spaces | `getRed()`, `getGreen()`, `getBlue()` and `withRed()`, `withGreen()`, `withBlue()` |
| `Hsl` | `getHue()`, `getSaturation()`, `getLightness()`, `withHue()`, `withSaturation()`, `withLightness()` |
| `Hwb` | `getHue()`, `getWhiteness()`, `getBlackness()`, `withHue()`, `withWhiteness()`, `withBlackness()` |
| `Lab`, `OkLab` | `getLightness()`, `getA()`, `getB()`, `withLightness()`, `withA()`, `withB()` |
| `Lch`, `OkLch` | `getLightness()`, `getChroma()`, `getHue()`, `withLightness()`, `withChroma()`, `withHue()` |
| `XyzD50`, `XyzD65` | `getX()`, `getY()`, `getZ()`, `withX()`, `withY()`, `withZ()` |

## Behavior notes

- Construction clamps alpha to `0..1`, wraps hue to `0..360`, and rejects non-finite channel values. Other channels generally remain unclamped to avoid clipping during conversion.
- CSS percentage reference ranges depend on the space: RGB profiles map to their component scale, RGB channels to `0..255`, and HSL/HWB saturation-like channels to `0..100`.
- Parsing clamps negative `lch()` and `oklch()` chroma to `0`.
- `label()` compares Euclidean channel distance in the current color space; it is not a perceptual guarantee unless the chosen space provides that property.
- `to()` and `fitGamut()` throw an `InvalidArgumentException` for unsupported non-empty space names.

## Related

- [Utilities](index.md)
- [Formatter](formatter.md)
- [Images](image.md)

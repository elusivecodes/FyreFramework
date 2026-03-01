# Colors

`Color` (`Fyre\Utility\Color\Color`) provides parsing, formatting, conversion, and UI-oriented helpers for working with CSS-style colors. Concrete color spaces are represented by `Colors\*` classes such as `Srgb`, `Lab`, and `XyzD65`.


## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [Constants](#constants)
- [Supported color spaces](#supported-color-spaces)
- [Parsing and creating colors](#parsing-and-creating-colors)
  - [Parse a CSS color string](#parse-a-css-color-string)
  - [Create colors from channels](#create-colors-from-channels)
- [Converting colors](#converting-colors)
  - [Convert to a named space](#convert-to-a-named-space)
  - [Fit a color to a target gamut](#fit-a-color-to-a-target-gamut)
- [Formatting colors](#formatting-colors)
- [Method guide](#method-guide)
  - [Parsing and factories](#parsing-and-factories)
  - [Conversion](#conversion)
  - [Formatting and output](#formatting-and-output)
  - [Alpha and helpers](#alpha-and-helpers)
  - [Channel accessors (by space)](#channel-accessors-by-space)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use `Color` when you need to accept CSS-like color input, convert between spaces (including wide-gamut profiles), and produce consistent CSS output strings.

This API is intentionally “space-aware”: each instance has a current color space, can convert to other spaces, and formats to the appropriate CSS representation for that space.

## Quick start

```php
use Fyre\Utility\Color\Color;
use Fyre\Utility\Color\Colors\Srgb;

$raw = 'hsl(210 90% 55% / 70%)';

// Parse without forcing a target space (returns a concrete Colors\* instance).
$color = Color::createFromString($raw);

// Parse and normalize into a specific space.
$srgb = Srgb::createFromString($raw);

// Convert to another space.
$lab = $srgb->to('lab');

// Accessibility helpers.
$bg = Color::createFromString('#0f172a');
$contrast = $bg->contrast(Color::createFromString('white'));
```

## Constants

`Color` exposes a public constant containing CSS named colors:

- `Color::CSS_COLORS` maps lowercase CSS color names (for example: `rebeccapurple`) to hex strings (for example: `#663399`).

This list is used by `label()` for nearest-name lookup, and can also be used for name-aware formatting in `Rgb::toString()` and `Hex::toString()`.

## Supported color spaces

Color spaces are implemented as concrete classes under `Fyre\Utility\Color\Colors`:

- Hex: `Hex`
- RGB (0–255-style channels): `Rgb`
- RGB-like 0–1 profiles: `Srgb`, `SrgbLinear`, `DisplayP3`, `DisplayP3Linear`, `A98Rgb`, `ProPhotoRgb`, `Rec2020`
- Polar/cylindrical: `Hsl`, `Hwb`
- Perceptual: `Lab`, `Lch`, `OkLab`, `OkLch`
- Reference: `XyzD50`, `XyzD65`

📌 In practice, most application code can stay in `srgb` and convert when you need a specific output format or analysis (wide-gamut output, perceptual comparisons, contrast checks, or gamut fitting).

When converting with `to(string $space)`, use one of these space identifiers:

```text
a98-rgb, display-p3, display-p3-linear, hex, hsl, hwb, lab, lch, oklab, oklch,
prophoto-rgb, rec2020, rgb, srgb, srgb-linear, xyz-d50, xyz-d65
```

## Parsing and creating colors

### Parse a CSS color string

Use `Color::createFromString()` when you want to accept typical CSS-like input:

```php
$raw = 'rgba(255, 0, 0, 0.5)';

// Returns a concrete space matching the input (Rgb here).
$color = Color::createFromString($raw);

// Parse and immediately normalize into a target space.
$srgb = Srgb::createFromString($raw);
```

Supported inputs include:

- Named CSS colors (for example: `"rebeccapurple"`, `"hotpink"`)
- `"transparent"` (parsed as RGB with `alpha = 0`)
- Hex strings: `#rgb`, `#rgba`, `#rrggbb`, `#rrggbbaa`
- Functional forms: `rgb(...)`, `rgba(...)`, `hsl(...)`, `hsla(...)`, `hwb(...)`, `lab(...)`, `lch(...)`, `oklab(...)`, `oklch(...)`
- `color(...)` values:
  - `color(srgb ...)`, `color(srgb-linear ...)`
  - `color(display-p3 ...)`, `color(display-p3-linear ...)`
  - `color(a98-rgb ...)`, `color(prophoto-rgb ...)`, `color(rec2020 ...)`
  - `color(xyz-d50 ...)`, `color(xyz ...)` / `color(xyz-d65 ...)`

Parsing details:

- Whitespace is normalized and parsing is case-insensitive.
- Percent values are supported where CSS allows them.
- CSS angle parsing supports plain degrees plus `%`, `grad`, `rad`, and `turn` units.

### Create colors from channels

If you already have numeric channels, use the `createFrom*()` factories. These factories are named for the source space, and the class you call them on determines the returned space.

```php
use Fyre\Utility\Color\Colors\Lab;

// Create sRGB directly from sRGB channels (0..1).
$brand = Srgb::createFromSrgb(0.12, 0.56, 0.92);

// Create Lab by converting from sRGB channels.
$brandLab = Lab::createFromSrgb(0.12, 0.56, 0.92);

// Create sRGB by converting from HSL channels (hue in degrees, S/L in 0..100).
$accent = Srgb::createFromHsl(330, 85, 55, 0.8);
```

## Converting colors

### Convert to a named space

Use `to(string $space)` to convert a color into a supported space name:

```php
$c = Color::createFromString('#0ea5e9');

$lab = $c->to('lab');
$p3 = $c->to('display-p3');
```

Convenience methods also exist for common conversions, such as `toHex()`, `toRgb()`, `toHsl()`, `toSrgb()`, `toXyzD65()`, and others.

### Fit a color to a target gamut

Use `fitGamut()` to fit the color into a target space’s gamut by reducing OKLCH chroma. The returned color is in the current color space.

📌 Use this when you have a wide-gamut color (for example `display-p3`) but you need to output CSS for a smaller target gamut (most commonly `srgb`).

```php
use Fyre\Utility\Color\Colors\DisplayP3;

$p3 = DisplayP3::createFromString('color(display-p3 1 0.2 0.2)');
$fitted = $p3->fitGamut('srgb'); // still DisplayP3, but adjusted to fit sRGB gamut
```

## Formatting colors

All colors are stringable; `__toString()` calls `toString()`:

```php
$c = Color::createFromString('hsl(210 90% 55% / 70%)');

(string) $c;
$c->toHex()->toString();
```

Formatting depends on the concrete color space:

- `Hex` formats as `#rgb/#rgba/#rrggbb/#rrggbbaa` (optionally shortened)
- `Rgb` formats as `rgb(r g b / a%)`
- `Hsl`, `Hwb`, `Lab`, `Lch`, `OkLab`, `OkLch` format as their CSS functional form
- Other spaces format as `color(<space> <c1> <c2> <c3> / <alpha>)`

📌 Common workflow: parse input → normalize to the output space → format as CSS.

```php
$css = Color::createFromString('color(display-p3 1 0.2 0.2)')
    ->fitGamut('srgb')
    ->toSrgb()
    ->toString();
```

## Method guide

### Parsing and factories

#### **Parse a CSS color string** (`createFromString()`)

Parses a CSS-like color string and returns a concrete `Colors\*` instance. When called as `Color::createFromString()`, the returned type generally matches the input format; when called as `<Space>::createFromString()`, the parsed color is converted into that space.

Arguments:
- `$string` (`string`): the CSS-like color string.

Throws:
- `InvalidArgumentException` if the string is not a supported/valid color format.

```php
$any = Color::createFromString('#0ea5e9');
$srgb = Srgb::createFromString('#0ea5e9');
```

#### **Create from channels in a specific source space** (`createFrom*()`)

Creates a color from explicit channel values. These factories are defined on `Color` and inherited by all spaces; call them on the class you want back:

- `createFromRgb(float $red, float $green, float $blue, float $alpha = 1)`
- `createFromHex(...)` is not provided; use `createFromString('#...')` or `toHex()`
- `createFromSrgb(float $red, float $green, float $blue, float $alpha = 1)`
- `createFromSrgbLinear(float $red, float $green, float $blue, float $alpha = 1)`
- `createFromDisplayP3(float $red, float $green, float $blue, float $alpha = 1)`
- `createFromDisplayP3Linear(float $red, float $green, float $blue, float $alpha = 1)`
- `createFromA98Rgb(float $red, float $green, float $blue, float $alpha = 1)`
- `createFromProPhotoRgb(float $red, float $green, float $blue, float $alpha = 1)`
- `createFromRec2020(float $red, float $green, float $blue, float $alpha = 1)`
- `createFromHsl(float $hue, float $saturation, float $lightness, float $alpha = 1)`
- `createFromHwb(float $hue, float $whiteness, float $blackness, float $alpha = 1)`
- `createFromLab(float $lightness, float $a, float $b, float $alpha = 1)`
- `createFromLch(float $lightness, float $chroma, float $hue, float $alpha = 1)`
- `createFromOkLab(float $lightness, float $a, float $b, float $alpha = 1)`
- `createFromOkLch(float $lightness, float $chroma, float $hue, float $alpha = 1)`
- `createFromXyzD50(float $x, float $y, float $z, float $alpha = 1)`
- `createFromXyzD65(float $x, float $y, float $z, float $alpha = 1)`

```php
$srgb = Srgb::createFromRgb(14, 165, 233);
$lab = Lab::createFromRgb(14, 165, 233);
```

### Conversion

#### **Get the current space name** (`space()`)

Returns the color space identifier for the current concrete class (for example: `srgb`, `lab`, `xyz-d65`).

```php
$c = Color::createFromString('#0ea5e9');
$space = $c->space();
```

#### **Convert to a named space** (`to()`)

Converts the color to one of the supported space names.

Arguments:
- `$space` (`string`): the target space name (for example: `lab`, `display-p3`, `xyz-d65`).

```php
$c = Color::createFromString('#0ea5e9');
$xyz = $c->to('xyz-d65');
```

#### **Fit to a target gamut** (`fitGamut()`)

Fits the color into the target gamut by reducing OKLCH chroma. Supported target spaces are:

- `a98-rgb`, `display-p3`, `display-p3-linear`, `prophoto-rgb`, `rec2020`, `rgb`, `srgb`, `srgb-linear`

Arguments:
- `$space` (`string`): the target gamut space.

```php
$c = Color::createFromString('color(display-p3 1 0.2 0.2)');
$fitted = $c->fitGamut('srgb');
```

### Formatting and output

#### **Format as CSS** (`toString()`)

Returns a CSS color string appropriate to the concrete color space. By default, alpha is included if it is less than `1`.

Arguments:
- `$alpha` (`bool|null`): whether to include the alpha component (`null` uses the default rule).
- `$precision` (`int`): Decimal precision for formatted components.

```php
$c = Color::createFromString('lab(50% 60 30 / 0.7)');
$s = $c->toString();
```

`Rgb` and `Hex` also support additional formatting options:

- `Rgb::toString(..., bool $name = false)` optionally emits CSS names (or `transparent`) when possible.
- `Hex::toString(..., bool $shortenHex = true, bool $name = false)` optionally shortens hex output and/or emits names.

#### **String casting** (`__toString()`)

Casts the color to a string by calling `toString()`.

```php
$c = Color::createFromString('#0ea5e9');
$s = (string) $c;
```

### Alpha and helpers

#### **Read alpha** (`getAlpha()`)

Returns the current alpha value.

```php
$c = Color::createFromString('rgb(0 0 0 / 50%)');
$a = $c->getAlpha();
```

#### **Clone with alpha** (`withAlpha()`)

Clones the color with a new alpha value.

Arguments:
- `$alpha` (`float`): the new alpha value.

```php
$c = Color::createFromString('#0ea5e9');
$semi = $c->withAlpha(0.5);
```

#### **Relative luminance** (`luma()`)

Returns the relative luminance, computed via sRGB.

```php
$c = Color::createFromString('#0ea5e9');
$l = $c->luma();
```

#### **Contrast ratio** (`contrast()`)

Calculates the contrast ratio between this color and another color using relative luminance.

Arguments:
- `$other` (`Color`): the other color.

```php
$bg = Color::createFromString('#0f172a');
$ratio = $bg->contrast(Color::createFromString('white'));
```

#### **Nearest CSS name** (`label()`)

Returns the closest CSS named color by comparing channel distance within the current color space.

```php
$c = Color::createFromString('#663399');
$name = $c->label(); // "rebeccapurple"
```

### Channel accessors (by space)

All concrete colors expose their components via `toArray()`:

- `toArray()` returns an associative array of components for the current space (including `alpha`). The keys depend on the concrete class (for example: `red/green/blue`, `hue/saturation/lightness`, `x/y/z`).

Many spaces also expose getters and “with*” cloning helpers:

- RGB-like (`Rgb`, `Srgb`, `SrgbLinear`, `DisplayP3`, `DisplayP3Linear`, `A98Rgb`, `ProPhotoRgb`, `Rec2020`):
  - `getRed()`, `getGreen()`, `getBlue()`
  - `withRed()`, `withGreen()`, `withBlue()`
- `Hsl`:
  - `getHue()`, `getSaturation()`, `getLightness()`
  - `withHue()`, `withSaturation()`, `withLightness()`
- `Hwb`:
  - `getHue()`, `getWhiteness()`, `getBlackness()`
  - `withHue()`, `withWhiteness()`, `withBlackness()`
- `Lab` and `OkLab`:
  - `getLightness()`, `getA()`, `getB()`
  - `withLightness()`, `withA()`, `withB()`
- `Lch` and `OkLch`:
  - `getLightness()`, `getChroma()`, `getHue()`
  - `withLightness()`, `withChroma()`, `withHue()`
- `XyzD50` and `XyzD65`:
  - `getX()`, `getY()`, `getZ()`
  - `withX()`, `withY()`, `withZ()`

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Alpha values are clamped to `0..1` on construction, and hue values are wrapped to `0..360`.
- Other channels are required to be finite numbers, but are not generally clamped (to avoid conversion clipping).
- `Color::createFromString()` normalizes whitespace and is case-insensitive, and `"transparent"` is treated as an RGB color with alpha `0`.
- Percent values follow CSS-style reference ranges (space-specific).
- In `rgb()` / `rgba()`, channel percentages are relative to `255`.
- In `hsl()` / `hsla()` / `hwb()`, percentages map directly to `0..100`.
- In `lab()` / `lch()`, lightness percentages map to `0..100` and other channels use CSS reference ranges.
- In `oklab()` / `oklch()`, percentages map to the CSS reference ranges for those spaces.
- In `color(<profile> ...)`, percentages map to `0..1` component values.
- For `lch()` and `oklch()`, negative chroma values are clamped to `0` during parsing.
- `label()` compares named colors within the current space; convert first if you need labeling in a specific space (for example: `->toSrgb()->label()`).
- `to()` and `fitGamut()` throw `InvalidArgumentException` for unsupported space names.

## Related

- [Utilities](index.md)
- [Formatter](formatter.md)

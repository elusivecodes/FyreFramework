# Math

Use `Math` when you need common numeric operations, interpolation, range mapping, random values, base conversion, or trigonometry through a consistent static API.

Most methods wrap PHP's built-in math functions. Fyre also provides helpers for clamping, interpolation, range mapping, distances, and step rounding.

## Table of Contents

- [Start here](#start-here)
- [Constants](#constants)
- [Method guide](#method-guide)
  - [Values and rounding](#values-and-rounding)
  - [Ranges and interpolation](#ranges-and-interpolation)
  - [Geometry and trigonometry](#geometry-and-trigonometry)
  - [Random values](#random-values)
  - [Number bases](#number-bases)
  - [Powers and logarithms](#powers-and-logarithms)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

All methods are static:

```php
use Fyre\Utility\Math;

$percentage = Math::clampPercent(125);       // 100
$progress = Math::map(75, 0, 100, 0, 1);     // 0.75
$rounded = Math::toStep(12.37, 0.25);        // 12.25
$distance = Math::dist(0, 0, 3, 4);          // 5.0
```

Use the constants when an operation needs a standard mathematical or platform boundary:

```php
$circumference = Math::TAU * $radius;
$radians = 45 * Math::PI / 180;
```

## Constants

| Constant | Description |
| --- | --- |
| `E` | Euler's number |
| `EPSILON` | the smallest representable difference used for floating-point comparisons |
| `FLOAT_MAX` | the largest representable floating-point value |
| `FLOAT_MIN` | the smallest positive normalized floating-point value |
| `HALF_PI` | one-half of pi |
| `INFINITY` | positive infinity |
| `INT_MAX` | the largest platform integer |
| `INT_MIN` | the smallest platform integer |
| `PI` | pi |
| `QUARTER_PI` | one-quarter of pi |
| `TAU` | two times pi |
| `TWO_PI` | an alias of `TAU` |

## Method guide

### Values and rounding

#### **Get an absolute value** (`abs()`)

Returns the non-negative value of a number.

Arguments:
- `$number` (`float|int`): the input number.

```php
$value = Math::abs(-12.5); // 12.5
```

#### **Find minimum and maximum values** (`min()`, `max()`)

Returns the lowest or highest value from one or more numbers.

Arguments:
- `$numbers` (`float|int ...`): the numbers to compare.

```php
$min = Math::min(8, 3, 5); // 3
$max = Math::max(8, 3, 5); // 8
```

#### **Calculate a sum or product** (`sum()`, `product()`)

Returns the sum or product of the supplied numbers.

Arguments:
- `$numbers` (`float|int ...`): the numbers to combine.

```php
$sum = Math::sum(2, 3, 4);         // 9
$product = Math::product(2, 3, 4); // 24
```

#### **Check whether a value is numeric** (`isNumeric()`)

Returns whether PHP considers a value numeric, including numeric strings.

Arguments:
- `$value` (`mixed`): the value to test.

```php
$valid = Math::isNumeric('12.5'); // true
```

#### **Round up or down** (`ceil()`, `floor()`)

Rounds a number toward positive or negative infinity.

Arguments:
- `$number` (`float|int`): the input number.

```php
$up = Math::ceil(4.2);    // 5.0
$down = Math::floor(4.8); // 4.0
```

#### **Round with a mode** (`round()`)

Rounds a number to a decimal precision using a PHP `RoundingMode`.

Arguments:
- `$number` (`float|int`): the input number.
- `$precision` (`int`): the number of decimal digits (defaults to `0`).
- `$mode` (`RoundingMode`): the rounding mode (defaults to `RoundingMode::HalfAwayFromZero`).

```php
use RoundingMode;

$value = Math::round(12.345, 2); // 12.35
$even = Math::round(12.5, mode: RoundingMode::HalfEven); // 12.0
```

#### **Round to a step** (`toStep()`)

Rounds a number to the nearest multiple of a step.

Arguments:
- `$number` (`float|int`): the input number.
- `$step` (`float|int`): the step size (defaults to `0.01`).

```php
$price = Math::toStep(12.37, 0.25); // 12.25
```

### Ranges and interpolation

#### **Clamp a value** (`clamp()`)

Constrains a number to a minimum and maximum.

Arguments:
- `$number` (`float|int`): the input number.
- `$min` (`float|int`): the minimum value (defaults to `0`).
- `$max` (`float|int`): the maximum value (defaults to `1`).

```php
$value = Math::clamp(12, 0, 10); // 10
```

#### **Clamp a percentage** (`clampPercent()`)

Constrains a number to the range from `0` through `100`.

Arguments:
- `$number` (`float|int`): the input number.

```php
$percentage = Math::clampPercent(-5); // 0
```

#### **Interpolate between values** (`lerp()`)

Returns a value at a given amount between two endpoints.

Arguments:
- `$v1` (`float`): the first endpoint.
- `$v2` (`float`): the second endpoint.
- `$amount` (`float`): the interpolation amount.

```php
$value = Math::lerp(10, 20, 0.25); // 12.5
```

#### **Find an interpolation amount** (`inverseLerp()`)

Returns where a value falls between two endpoints.

Arguments:
- `$v1` (`float`): the first endpoint.
- `$v2` (`float`): the second endpoint.
- `$value` (`float`): the value to locate.

```php
$amount = Math::inverseLerp(10, 20, 12.5); // 0.25
```

If both endpoints are equal, this method returns `0.0`.

#### **Map between ranges** (`map()`)

Maps a number from one range into another.

Arguments:
- `$number` (`float`): the input number.
- `$fromMin` (`float`): the source minimum.
- `$fromMax` (`float`): the source maximum.
- `$toMin` (`float`): the target minimum.
- `$toMax` (`float`): the target maximum.

```php
$progress = Math::map(75, 0, 100, 0, 1); // 0.75
```

If the source endpoints are equal, this method returns `$toMin`.

### Geometry and trigonometry

#### **Calculate the distance between points** (`dist()`)

Returns the Euclidean distance between two 2D points.

Arguments:
- `$x1` (`float`): the first X coordinate.
- `$y1` (`float`): the first Y coordinate.
- `$x2` (`float`): the second X coordinate.
- `$y2` (`float`): the second Y coordinate.

```php
$distance = Math::dist(0, 0, 3, 4); // 5.0
```

#### **Calculate a vector length** (`hypot()`)

Returns the Euclidean length of a 2D vector.

Arguments:
- `$x` (`float`): the X component.
- `$y` (`float`): the Y component.

```php
$length = Math::hypot(3, 4); // 5.0
```

#### **Convert angles** (`degreesToRadians()`, `radiansToDegrees()`)

Converts an angle between degrees and radians.

| Method | Signature |
| --- | --- |
| `degreesToRadians()` | `degreesToRadians(float $number): float` |
| `radiansToDegrees()` | `radiansToDegrees(float $number): float` |

```php
$radians = Math::degreesToRadians(180); // approximately 3.14159
$degrees = Math::radiansToDegrees(Math::PI); // 180.0
```

#### **Use trigonometric functions** (`sin()`, `cos()`, `tan()`)

All trigonometric inputs and inverse-function results use radians.

| Method | Result |
| --- | --- |
| `sin()` | sine of `$number` |
| `cos()` | cosine of `$number` |
| `tan()` | tangent of `$number` |
| `asin()` | arc sine of `$number` |
| `acos()` | arc cosine of `$number` |
| `atan()` | arc tangent of `$number` |
| `atan2()` | arc tangent of `$y / $x`, using the signs of both coordinates |

```php
$sine = Math::sin(Math::HALF_PI); // 1.0
$angle = Math::atan2(1, 1);       // approximately 0.7854
```

#### **Use hyperbolic functions** (`sinh()`, `cosh()`, `tanh()`)

| Method | Result |
| --- | --- |
| `sinh()` | hyperbolic sine of `$number` |
| `cosh()` | hyperbolic cosine of `$number` |
| `tanh()` | hyperbolic tangent of `$number` |
| `asinh()` | inverse hyperbolic sine of `$number` |
| `acosh()` | inverse hyperbolic cosine of `$number` |
| `atanh()` | inverse hyperbolic tangent of `$number` |

### Random values

#### **Generate a random float** (`random()`)

Returns a random floating-point value. With no arguments, the range is `0` through `1`. With one argument, the other boundary is `0`. With two arguments, their order does not matter.

Arguments:
- `$a` (`float|null`): the first range boundary.
- `$b` (`float|null`): the second range boundary.

```php
$ratio = Math::random();       // 0.0 through 1.0
$offset = Math::random(-5);    // -5.0 through 0.0
$score = Math::random(10, 20); // 10.0 through 20.0
```

#### **Generate a random integer** (`randomInt()`)

Returns a cryptographically secure random integer. With one argument, the other boundary is `0`.

Arguments:
- `$a` (`int`): the first range boundary.
- `$b` (`int|null`): the second range boundary.

```php
$digit = Math::randomInt(9);       // 0 through 9
$offset = Math::randomInt(-5);     // -5 through 0
$score = Math::randomInt(10, 20);  // 10 through 20
```

### Number bases

#### **Convert between bases** (`convertBase()`)

Converts an integer or numeric string between bases `2` through `36`.

Arguments:
- `$number` (`int|string`): the value to convert.
- `$fromBase` (`int`): the source base.
- `$toBase` (`int`): the target base.

```php
$binary = Math::convertBase('ff', 16, 2); // "11111111"
```

#### **Use common base conversions** (`binaryToDecimal()`, `decimalToBinary()`)

Convenience methods cover the common binary, octal, decimal, and hexadecimal conversions.

| Method | Example result |
| --- | --- |
| `binaryToDecimal()` | `Math::binaryToDecimal('1010')` returns `10` |
| `decimalToBinary()` | `Math::decimalToBinary(10)` returns `"1010"` |
| `decimalToHex()` | `Math::decimalToHex(255)` returns `"ff"` |
| `decimalToOctal()` | `Math::decimalToOctal(8)` returns `"10"` |
| `hexToDecimal()` | `Math::hexToDecimal('ff')` returns `255` |
| `octalToDecimal()` | `Math::octalToDecimal('10')` returns `8` |

### Powers and logarithms

#### **Calculate powers and roots** (`pow()`, `sqrt()`)

| Method | Result |
| --- | --- |
| `pow(float|int $number, float|int $exponent): float|int` | raises a number to an exponent |
| `sqrt(float $number): float` | calculates a square root |

```php
$power = Math::pow(2, 8); // 256
$root = Math::sqrt(81);   // 9.0
```

#### **Calculate exponential values** (`exp()`, `expMinus1()`)

| Method | Result |
| --- | --- |
| `exp(float $number): float` | calculates `E` raised to a number |
| `expMinus1(float $number): float` | calculates `E` raised to a number, minus `1` |

Use `expMinus1()` when the input is close to zero and precision matters.

#### **Calculate logarithms** (`log()`, `log10()`, `logPlus1()`)

| Method | Result |
| --- | --- |
| `log(float $number, float $base = Math::E): float` | calculates a logarithm using the supplied base |
| `log10(float $number): float` | calculates a base-10 logarithm |
| `logPlus1(float $number): float` | calculates the natural logarithm of `1 + $number` |

```php
$natural = Math::log(Math::E); // 1.0
$decimal = Math::log10(1000);  // 3.0
```

Use `logPlus1()` when the input is close to zero and precision matters.

#### **Calculate a floating-point remainder** (`fmod()`)

Returns the floating-point remainder after division.

Arguments:
- `$number` (`float`): the dividend.
- `$divisor` (`float`): the divisor.

```php
$remainder = Math::fmod(5.5, 2); // 1.5
```

## Behavior notes

A few behaviors are worth keeping in mind:

- Trigonometric methods use radians unless the method name explicitly converts degrees.
- `lerp()`, `inverseLerp()`, and `map()` extrapolate values outside their input ranges; they do not clamp automatically.
- `inverseLerp()` returns `0.0` for equal endpoints, while `map()` returns the target minimum for an empty source range.
- `toStep()` treats a negative step as positive and returns the input as a float when the step is zero.
- `sum()` returns `0` and `product()` returns `1` when no values are supplied; `min()` and `max()` require at least one value.
- `binaryToDecimal()`, `hexToDecimal()`, and `octalToDecimal()` return a float when the result does not fit in a platform integer.
- `random()` and `randomInt()` include both range boundaries. When two boundaries are passed to `randomInt()`, the first must not exceed the second.
- `FLOAT_MIN` is the smallest positive normalized float, not the most negative float.

## Related

- [Utilities](index.md)
- [Collections](collections.md)
- [Formatter](formatter.md)

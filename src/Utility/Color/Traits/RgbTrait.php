<?php
declare(strict_types=1);

namespace Fyre\Utility\Color\Traits;

use Override;

/**
 * Provides RGB channel accessors.
 */
trait RgbTrait
{
    public readonly float $blue;

    public readonly float $green;

    public readonly float $red;

    /**
     * Constructs an RGB color.
     *
     * @param float $red The red value.
     * @param float $green The green value.
     * @param float $blue The blue value.
     * @param float $alpha The alpha value. (0, 1)
     */
    public function __construct(
        float $red = 0,
        float $green = 0,
        float $blue = 0,
        float $alpha = 1,
    ) {
        parent::__construct($alpha);

        static::ensureFinite($red);
        static::ensureFinite($green);
        static::ensureFinite($blue);

        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }

    /**
     * Returns the blue value.
     *
     * @return float The blue value.
     */
    public function getBlue(): float
    {
        return $this->blue;
    }

    /**
     * Returns the green value.
     *
     * @return float The green value.
     */
    public function getGreen(): float
    {
        return $this->green;
    }

    /**
     * Returns the red value.
     *
     * @return float The red value.
     */
    public function getRed(): float
    {
        return $this->red;
    }

    /**
     * {@inheritDoc}
     *
     * @return array{red: float, green: float, blue: float, alpha: float} The color components.
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'red' => $this->red,
            'green' => $this->green,
            'blue' => $this->blue,
            'alpha' => $this->alpha,
        ];
    }

    /**
     * Clones the Color with a new blue value.
     *
     * @param float $blue The blue value.
     * @return static The new Color instance with the updated blue value.
     */
    public function withBlue(float $blue): static
    {
        return new static($this->red, $this->green, $blue, $this->alpha);
    }

    /**
     * Clones the Color with a new green value.
     *
     * @param float $green The green value.
     * @return static The new Color instance with the updated green value.
     */
    public function withGreen(float $green): static
    {
        return new static($this->red, $green, $this->blue, $this->alpha);
    }

    /**
     * Clones the Color with a new red value.
     *
     * @param float $red The red value.
     * @return static The new Color instance with the updated red value.
     */
    public function withRed(float $red): static
    {
        return new static($red, $this->green, $this->blue, $this->alpha);
    }
}

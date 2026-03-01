<?php
declare(strict_types=1);

namespace Fyre\Utility\Color\Traits;

use Override;

/**
 * Provides LCH channel accessors.
 */
trait LchTrait
{
    public readonly float $chroma;

    public readonly float $hue;

    public readonly float $lightness;

    /**
     * Returns the chroma value.
     *
     * @return float The chroma value.
     */
    public function getChroma(): float
    {
        return $this->chroma;
    }

    /**
     * Returns the hue value.
     *
     * @return float The hue value.
     */
    public function getHue(): float
    {
        return $this->hue;
    }

    /**
     * Returns the lightness value.
     *
     * @return float The lightness value.
     */
    public function getLightness(): float
    {
        return $this->lightness;
    }

    /**
     * {@inheritDoc}
     *
     * @return array{lightness: float, chroma: float, hue: float, alpha: float} The color components.
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'lightness' => $this->lightness,
            'chroma' => $this->chroma,
            'hue' => $this->hue,
            'alpha' => $this->alpha,
        ];
    }

    /**
     * Clones the Color with a new chroma value.
     *
     * @param float $chroma The chroma value.
     * @return static The new Color instance with the updated chroma value.
     */
    public function withChroma(float $chroma): static
    {
        return new static($this->lightness, $chroma, $this->hue, $this->alpha);
    }

    /**
     * Clones the Color with a new hue value.
     *
     * @param float $hue The hue value.
     * @return static The new Color instance with the updated hue value.
     */
    public function withHue(float $hue): static
    {
        return new static($this->lightness, $this->chroma, $hue, $this->alpha);
    }

    /**
     * Clones the Color with a new lightness value.
     *
     * @param float $lightness The lightness value.
     * @return static The new Color instance with the updated lightness value.
     */
    public function withLightness(float $lightness): static
    {
        return new static($lightness, $this->chroma, $this->hue, $this->alpha);
    }
}

<?php
declare(strict_types=1);

namespace Fyre\Utility\Color\Traits;

use Override;

/**
 * Provides LAB channel accessors.
 */
trait LabTrait
{
    public readonly float $a;

    public readonly float $b;

    public readonly float $lightness;

    /**
     * Returns the a value.
     *
     * @return float The a value.
     */
    public function getA(): float
    {
        return $this->a;
    }

    /**
     * Returns the b value.
     *
     * @return float The b value.
     */
    public function getB(): float
    {
        return $this->b;
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
     * @return array{lightness: float, a: float, b: float, alpha: float} The color components.
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'lightness' => $this->lightness,
            'a' => $this->a,
            'b' => $this->b,
            'alpha' => $this->alpha,
        ];
    }

    /**
     * Clones the Color with a new a value.
     *
     * @param float $a The a value.
     * @return static The new Color instance with the updated a value.
     */
    public function withA(float $a): static
    {
        return new static($this->lightness, $a, $this->b, $this->alpha);
    }

    /**
     * Clones the Color with a new b value.
     *
     * @param float $b The b value.
     * @return static The new Color instance with the updated b value.
     */
    public function withB(float $b): static
    {
        return new static($this->lightness, $this->a, $b, $this->alpha);
    }

    /**
     * Clones the Color with a new lightness value.
     *
     * @param float $lightness The lightness value.
     * @return static The new Color instance with the updated lightness value.
     */
    public function withLightness(float $lightness): static
    {
        return new static($lightness, $this->a, $this->b, $this->alpha);
    }
}

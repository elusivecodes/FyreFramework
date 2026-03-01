<?php
declare(strict_types=1);

namespace Fyre\Utility\Color\Traits;

use Override;

/**
 * Provides XYZ channel accessors.
 */
trait XyzTrait
{
    public readonly float $x;

    public readonly float $y;

    public readonly float $z;

    /**
     * Constructs an XYZ color.
     *
     * @param float $x The x value.
     * @param float $y The y value.
     * @param float $z The z value.
     * @param float $alpha The alpha value. (0, 1)
     */
    public function __construct(
        float $x = 0,
        float $y = 0,
        float $z = 0,
        float $alpha = 1,
    ) {
        parent::__construct($alpha);

        static::ensureFinite($x);
        static::ensureFinite($y);
        static::ensureFinite($z);

        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    /**
     * Returns the x value.
     *
     * @return float The x value.
     */
    public function getX(): float
    {
        return $this->x;
    }

    /**
     * Returns the y value.
     *
     * @return float The y value.
     */
    public function getY(): float
    {
        return $this->y;
    }

    /**
     * Returns the z value.
     *
     * @return float The z value.
     */
    public function getZ(): float
    {
        return $this->z;
    }

    /**
     * {@inheritDoc}
     *
     * @return array{x: float, y: float, z: float, alpha: float} The color components.
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'z' => $this->z,
            'alpha' => $this->alpha,
        ];
    }

    /**
     * Clones the Color with a new x value.
     *
     * @param float $x The x value.
     * @return static The new Color instance with the updated x value.
     */
    public function withX(float $x): static
    {
        return new static($x, $this->y, $this->z, $this->alpha);
    }

    /**
     * Clones the Color with a new y value.
     *
     * @param float $y The y value.
     * @return static The new Color instance with the updated y value.
     */
    public function withY(float $y): static
    {
        return new static($this->x, $y, $this->z, $this->alpha);
    }

    /**
     * Clones the Color with a new z value.
     *
     * @param float $z The z value.
     * @return static The new Color instance with the updated z value.
     */
    public function withZ(float $z): static
    {
        return new static($this->x, $this->y, $z, $this->alpha);
    }
}

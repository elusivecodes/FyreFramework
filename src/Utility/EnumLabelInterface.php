<?php
declare(strict_types=1);

namespace Fyre\Utility;

/**
 * Provides a display label for an enum case.
 */
interface EnumLabelInterface
{
    /**
     * Returns the display label.
     *
     * @return string The display label.
     */
    public function label(): string;
}

<?php
declare(strict_types=1);

namespace Fyre\ORM\Attributes;

use Attribute;

/**
 * Attribute that declares the policy mapping for a model.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Policy
{
    /**
     * Constructs a Policy.
     *
     * @param string $name The policy name.
     */
    public function __construct(
        protected string $name,
    ) {}

    /**
     * Returns the policy name.
     *
     * @return string The policy name.
     */
    public function getName(): string
    {
        return $this->name;
    }
}

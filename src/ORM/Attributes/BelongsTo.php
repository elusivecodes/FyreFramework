<?php
declare(strict_types=1);

namespace Fyre\ORM\Attributes;

use Attribute;
use Fyre\ORM\Model;
use Override;

/**
 * Attribute that adds a belongs-to relationship to a model.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class BelongsTo extends ModelAttribute
{
    /**
     * Constructs a BelongsTo.
     *
     * @param string $name The relationship name.
     * @param array<string, mixed> $options The relationship options.
     */
    public function __construct(
        protected string $name,
        protected array $options = []
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function loadModel(Model $model): void
    {
        $model->belongsTo($this->name, $this->options);
    }
}

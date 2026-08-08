<?php
declare(strict_types=1);

namespace Fyre\ORM\Attributes;

use Attribute;
use Fyre\ORM\Model;
use Override;
use UnitEnum;

/**
 * Attribute that assigns an enum class to a model field.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class EnumField extends ModelAttribute
{
    /**
     * Constructs an EnumField.
     *
     * @param string $field The field name.
     * @param class-string<UnitEnum> $className The enum class name.
     */
    public function __construct(
        protected string $field,
        protected string $className
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function loadModel(Model $model): void
    {
        $model->getSchema()->setEnumClass($this->field, $this->className);
    }
}

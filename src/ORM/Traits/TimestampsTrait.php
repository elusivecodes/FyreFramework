<?php
declare(strict_types=1);

namespace Fyre\ORM\Traits;

use Fyre\Event\Event;
use Fyre\ORM\Entity;
use Fyre\ORM\Events\BeforeSave;
use Fyre\Utility\DateTime\DateTime;

/**
 * Adds automatic timestamp handling to ORM models.
 *
 * When saving, this sets the configured created field on new entities, and always updates
 * the configured modified field when those columns exist in the schema.
 */
trait TimestampsTrait
{
    protected string $createdField = 'created';

    protected string $modifiedField = 'modified';

    /**
     * Updates the timestamps on the entity.
     *
     * @param Event $event The Event.
     * @param Entity $entity The entity.
     */
    #[BeforeSave]
    public function handleTimestamps(Event $event, Entity $entity): void
    {
        $schema = $this->getSchema();

        if ($entity->isNew() && $schema->hasColumn($this->createdField)) {
            $entity->set($this->createdField, DateTime::now(), temporary: true);
        }

        if ($schema->hasColumn($this->modifiedField)) {
            $entity->set($this->modifiedField, DateTime::now(), temporary: true);
        }
    }
}

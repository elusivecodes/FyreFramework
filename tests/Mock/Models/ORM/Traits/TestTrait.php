<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM\Traits;

use ArrayObject;
use Fyre\Event\Event;
use Fyre\ORM\Entity;
use Fyre\ORM\Events\AfterDelete;
use Fyre\ORM\Events\AfterParse;
use Fyre\ORM\Events\AfterRules;
use Fyre\ORM\Events\AfterSave;
use Fyre\ORM\Events\BeforeDelete;
use Fyre\ORM\Events\BeforeParse;
use Fyre\ORM\Events\BeforeRules;
use Fyre\ORM\Events\BeforeSave;

use function is_string;
use function trim;

trait TestTrait
{
    protected string $testField = 'name';

    #[AfterDelete]
    public function afterDelete(Event $event, Entity $entity): void
    {
        if ($entity->get($this->testField) === 'failAfterDelete') {
            $event->setResult(false);

            return;
        }
    }

    #[AfterParse]
    public function afterParse(Event $event, Entity $entity): void
    {
        if ($entity->get($this->testField) === 'afterParse') {
            $entity->test = 1;
        }
    }

    #[AfterRules]
    public function afterRules(Event $event, Entity $entity): void
    {
        if ($entity->get($this->testField) === 'failAfterRules') {
            $event->setResult(false);

            return;
        }
    }

    #[AfterSave]
    public function afterSave(Event $event, Entity $entity): void
    {
        if ($entity->get($this->testField) === 'failAfterSave') {
            $event->setResult(false);

            return;
        }
    }

    #[BeforeDelete]
    public function beforeDelete(Event $event, Entity $entity): void
    {
        if ($entity->get($this->testField) === 'failBeforeDelete') {
            $event->setResult(false);

            return;
        }
    }

    #[BeforeParse]
    public function beforeParse(Event $event, ArrayObject $data): void
    {
        $testField = $this->testField;

        if ($data->offsetExists($testField) && is_string($data[$testField])) {
            $data[$testField] = trim($data[$testField]);
        }
    }

    #[BeforeRules]
    public function beforeRules(Event $event, Entity $entity): void
    {
        if ($entity->get($this->testField) === 'failBeforeRules') {
            $event->setResult(false);

            return;
        }
    }

    #[BeforeSave]
    public function beforeSave(Event $event, Entity $entity): void
    {
        if ($entity->get($this->testField) === 'failBeforeSave') {
            $event->setResult(false);

            return;
        }
    }
}

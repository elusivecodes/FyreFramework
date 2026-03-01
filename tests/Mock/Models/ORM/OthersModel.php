<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM;

use Fyre\Event\Event;
use Fyre\ORM\Events\AfterFind;
use Fyre\ORM\Events\BeforeFind;
use Fyre\ORM\Model;
use Fyre\ORM\Queries\SelectQuery;
use Fyre\ORM\Result;

class OthersModel extends Model
{
    #[AfterFind]
    public function afterFind(Event $event, Result $result): void
    {
        foreach ($result as $item) {
            $item->test = 'Test';
        }
    }

    #[BeforeFind]
    public function beforeFind(Event $event, SelectQuery $query): void
    {
        $query->where([
            'value' => 1,
        ]);
    }
}

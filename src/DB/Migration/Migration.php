<?php
declare(strict_types=1);

namespace Fyre\DB\Migration;

use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Forge\Forge;

/**
 * Provides a base class for database migrations.
 *
 * Implementations define schema changes executed by {@see MigrationRunner}.
 */
abstract class Migration
{
    use DebugTrait;

    /**
     * Constructs a Migration.
     *
     * @param Forge $forge The Forge.
     */
    public function __construct(
        protected Forge $forge
    ) {}
}

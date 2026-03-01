<?php
declare(strict_types=1);

namespace Fyre\DB;

/**
 * Defines database feature flags.
 *
 * Used for capability checks when generating SQL for different database engines.
 */
enum DbFeature
{
    case DeleteAlias;
    case DeleteJoin;
    case DeleteMultipleTables;
    case DeleteUsing;
    case InsertReturning;
    case UpdateFrom;
    case UpdateJoin;
    case UpdateMultipleTables;
}

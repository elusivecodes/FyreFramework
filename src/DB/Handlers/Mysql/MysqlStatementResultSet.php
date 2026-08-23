<?php
declare(strict_types=1);

namespace Fyre\DB\Handlers\Mysql;

use Fyre\DB\StatementResultSet;
use Override;

/**
 * Provides a MySQL {@see StatementResultSet} implementation with native type mapping.
 */
class MysqlStatementResultSet extends StatementResultSet
{
    #[Override]
    protected static array $types = [
        'BLOB' => 'binary',
        'DATE' => 'date',
        'DATETIME' => 'datetime',
        'DOUBLE' => 'float',
        'FLOAT' => 'float',
        'INT24' => 'integer',
        'LONG' => 'integer',
        'LONGBLOB' => 'binary',
        'LONGLONG' => 'integer',
        'MEDIUMBLOB' => 'binary',
        'NEWDATE' => 'date',
        'NEWDECIMAL' => 'decimal',
        'SHORT' => 'integer',
        'TIME' => 'time',
        'TIMESTAMP' => 'datetime',
        'TINY' => 'integer',
        'TINYBLOB' => 'binary',
    ];
}

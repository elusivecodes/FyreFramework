<?php
declare(strict_types=1);

namespace Fyre\DB\Schema\Handlers\Mysql;

use Fyre\DB\QueryLiteral;
use Fyre\DB\Schema\Column;
use Fyre\DB\Type;
use Fyre\DB\TypeParser;
use Override;
use UnitEnum;

/**
 * Provides MySQL column metadata.
 */
class MysqlColumn extends Column
{
    /**
     * @var array<string, string>
     */
    #[Override]
    protected static array $types = [
        'bigint' => 'integer',
        'binary' => 'binary',
        'blob' => 'binary',
        'boolean' => 'boolean',
        'date' => 'date',
        'datetime' => 'datetime',
        'decimal' => 'decimal',
        'double' => 'float',
        'enum' => 'enum',
        'float' => 'float',
        'int' => 'integer',
        'json' => 'json',
        'longblob' => 'binary',
        'longtext' => 'text',
        'mediumblob' => 'binary',
        'mediumint' => 'integer',
        'mediumtext' => 'text',
        'set' => 'set',
        'smallint' => 'integer',
        'text' => 'text',
        'time' => 'time',
        'timestamp' => 'datetime',
        'tinyblob' => 'binary',
        'tinyint' => 'integer',
        'tinytext' => 'text',
        'varbinary' => 'binary',
    ];

    /**
     * Constructs a MysqlColumn.
     *
     * @param MysqlTable $table The MysqlTable.
     * @param TypeParser $typeParser The TypeParser.
     * @param string $name The column name.
     * @param string $type The column type.
     * @param int|null $length The column length.
     * @param int|null $precision The column precision.
     * @param bool $nullable Whether the column is nullable.
     * @param bool $unsigned Whether the column is unsigned.
     * @param bool|float|int|QueryLiteral|string|null $default The column default value.
     * @param string|null $comment The column comment.
     * @param bool $autoIncrement Whether the column is auto-incrementing.
     * @param class-string<UnitEnum>|null $enumClass The enum class.
     * @param string[]|null $values The column values.
     * @param string|null $charset The column character set.
     * @param string|null $collation The column collation.
     */
    public function __construct(
        MysqlTable $table,
        TypeParser $typeParser,
        string $name,
        string $type,
        int|null $length = null,
        int|null $precision = null,
        int|null $scale = null,
        int|null $fractionalSeconds = null,
        bool $nullable = false,
        bool $unsigned = false,
        bool|float|int|QueryLiteral|string|null $default = null,
        string|null $comment = null,
        bool $autoIncrement = false,
        string|null $enumClass = null,
        protected array|null $values = null,
        protected string|null $charset = null,
        protected string|null $collation = null,
    ) {
        parent::__construct(
            $table,
            $typeParser,
            $name,
            $type,
            $length,
            $precision,
            $scale,
            $fractionalSeconds,
            $nullable,
            $unsigned,
            $default,
            $comment,
            $autoIncrement,
            $enumClass
        );
    }

    /**
     * Returns the column character set.
     *
     * @return string|null The column character set.
     */
    public function getCharset(): string|null
    {
        return $this->charset;
    }

    /**
     * Returns the column collation.
     *
     * @return string|null The column collation.
     */
    public function getCollation(): string|null
    {
        return $this->collation;
    }

    /**
     * Returns the column enum values.
     *
     * @return string[]|null The column enum values.
     */
    public function getValues(): array|null
    {
        return $this->values;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'length' => $this->length,
            'precision' => $this->precision,
            'scale' => $this->scale,
            'fractionalSeconds' => $this->fractionalSeconds,
            'values' => $this->values,
            'nullable' => $this->nullable,
            'unsigned' => $this->unsigned,
            'default' => $this->default,
            'charset' => $this->charset,
            'collation' => $this->collation,
            'comment' => $this->comment,
            'autoIncrement' => $this->autoIncrement,
            'enumClass' => $this->enumClass,
        ];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function type(): Type
    {
        if ($this->type === 'tinyint' && $this->precision == 1) {
            return $this->typeParser->use('boolean');
        }

        return parent::type();
    }
}

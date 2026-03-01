<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Console;

use Override;

use function array_map;
use function implode;
use function preg_quote;
use function sprintf;

/**
 * PHPUnit constraint asserting console output contains a table row.
 */
class ContentsContainsRow extends ContentsRegExp
{
    /**
     * Constructs a ContentsContainsRow.
     *
     * @param mixed[] $row The expected row.
     * @param string $output The output type.
     */
    public function __construct(
        protected array $row,
        string $output
    ) {
        $row = array_map(
            static fn(mixed $cell): string => preg_quote((string) $cell, '/'),
            $row
        );

        $pattern = '/^\|\s'.implode('\s|\s+', $row).'\s\|$/';

        parent::__construct($pattern, $output);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf(
            'contains the row "%s"',
            implode(',', $this->row)
        );
    }
}

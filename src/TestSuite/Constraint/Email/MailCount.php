<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Email;

use Override;
use PHPUnit\Framework\Constraint\Count;

use function sprintf;

/**
 * PHPUnit constraint asserting the number of sent mails.
 */
class MailCount extends Count
{
    /**
     * Constructs a MailCount.
     *
     * @param int $expectedCount The expected count.
     */
    public function __construct(
        protected int $expectedCount
    ) {
        parent::__construct($expectedCount);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function failureDescription(mixed $other): string
    {
        return sprintf(
            'actual sent emails %s matches expected count %d',
            (int) $this->getCountOf($other),
            $this->expectedCount
        );
    }
}

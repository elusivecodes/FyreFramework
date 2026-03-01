<?php
declare(strict_types=1);

namespace Fyre\View\Helpers;

use Fyre\Utility\Formatter;
use Fyre\View\Helper;
use Fyre\View\View;

/**
 * Formats values for views.
 */
class FormatHelper extends Helper
{
    /**
     * Constructs a FormatHelper.
     *
     * @param Formatter $formatter The Formatter.
     * @param View $view The View.
     * @param array<string, mixed> $options The helper options.
     */
    public function __construct(
        protected Formatter $formatter,
        View $view,
        array $options = []
    ) {
        parent::__construct($view, $options);
    }

    /**
     * Calls a Formatter method.
     *
     * Note: This forwards the call to the underlying {@see Formatter} instance.
     *
     * @param string $method The method.
     * @param array<mixed> $arguments Arguments to pass to the method.
     * @return mixed The formatted value.
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->formatter->$method(...$arguments);
    }
}

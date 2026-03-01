<?php
declare(strict_types=1);

namespace Fyre\View;

use Fyre\Core\Traits\DebugTrait;

use function array_replace;

/**
 * Provides a base class for view helpers.
 *
 * Helper configuration is merged with any helper-specific defaults at construction time.
 */
abstract class Helper
{
    use DebugTrait;

    /**
     * @var array<string, mixed>
     */
    protected static array $defaults = [];

    /**
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * Constructs a Helper.
     *
     * @param View $view The View.
     * @param array<string, mixed> $options The helper options.
     */
    public function __construct(
        protected View $view,
        array $options = []
    ) {
        $this->config = array_replace(static::$defaults, $options);
    }

    /**
     * Returns the helper config.
     *
     * @return array<string, mixed> The helper config.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Returns the View.
     *
     * @return View The View.
     */
    public function getView(): View
    {
        return $this->view;
    }
}

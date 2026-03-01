<?php
declare(strict_types=1);

namespace Fyre\View\Helpers;

use Fyre\Http\Uri;
use Fyre\Router\Router;
use Fyre\Utility\HtmlHelper;
use Fyre\View\Helper;
use Fyre\View\View;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Generates URLs for views.
 *
 * Supports building URLs from named routes or from relative paths.
 */
class UrlHelper extends Helper
{
    protected ServerRequestInterface $request;

    /**
     * Constructs a UrlHelper.
     *
     * @param Router $router The Router.
     * @param View $view The View.
     * @param array<string, mixed> $options The helper options.
     */
    public function __construct(
        protected Router $router,
        protected HtmlHelper $htmlHelper,
        View $view,
        array $options = []
    ) {
        parent::__construct($view, $options);

        $this->request = $this->view->getRequest();
    }

    /**
     * Generates an anchor link for a destination.
     *
     * @param string $content The link content.
     * @param array<string, mixed> $attributes The link attributes.
     * @param bool $escape Whether to escape the link content.
     * @return string The anchor link.
     */
    public function link(string $content, array $attributes = [], bool $escape = true): string
    {
        if ($escape) {
            $content = $this->htmlHelper->escape($content);
        }

        return '<a'.$this->htmlHelper->attributes($attributes).'>'.$content.'</a>';
    }

    /**
     * Generates a URL for a relative path.
     *
     * Note: When `$full` is true and a base URI is configured, the path is resolved relative
     * to that base URI.
     *
     * @param string $path The relative path.
     * @param bool $full Whether to use a full URL.
     * @return string The URL.
     */
    public function path(string $path, bool $full = false): string
    {
        if ($full) {
            $baseUri = $this->router->getBaseUri();

            if ($baseUri) {
                return Uri::createFromString($baseUri)
                    ->resolveRelativeUri($path)
                    ->getUri();
            }
        }

        return Uri::createFromString($path)
            ->getUri();
    }

    /**
     * Generates a URL for a named route.
     *
     * @param string $name The name.
     * @param array<string, mixed> $arguments The route arguments.
     * @param string|null $scheme The route scheme.
     * @param string|null $host The route host.
     * @param int|null $port The route port.
     * @param bool|null $full Whether to use a full URL.
     * @return string The URL.
     */
    public function to(
        string $name,
        array $arguments = [],
        string|null $scheme = null,
        string|null $host = null,
        int|null $port = null,
        bool|null $full = null
    ): string {
        return $this->router->url(
            $name,
            $arguments,
            $scheme,
            $host,
            $port,
            $full
        );
    }
}

<?php
declare(strict_types=1);

namespace Fyre\Core\Attributes;

use Attribute;
use Fyre\Core\Container;
use Fyre\Core\ContextualAttribute;
use Fyre\Mail\Mailer;
use Fyre\Mail\MailManager;

/**
 * Resolves a mailer for contextual injection.
 *
 * @extends ContextualAttribute<Mailer>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Mail extends ContextualAttribute
{
    /**
     * Constructs a Mail attribute.
     *
     * @param string $key The mailer key.
     */
    public function __construct(
        protected string $key = MailManager::DEFAULT
    ) {}

    /**
     * Resolves the Mailer for contextual injection.
     *
     * @param Container $container The Container.
     * @return Mailer The Mailer instance for the mailer key.
     */
    public function resolve(Container $container): Mailer
    {
        return $container->use(MailManager::class)->use($this->key);
    }
}

<?php
declare(strict_types=1);

namespace Fyre\Core\Attributes;

use Attribute;
use Fyre\Core\Container;
use Fyre\Core\ContextualAttribute;
use Fyre\Security\Encryption\Encrypter;
use Fyre\Security\Encryption\EncryptionManager;

/**
 * Resolves an encrypter for contextual injection.
 *
 * @extends ContextualAttribute<Encrypter>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Encryption extends ContextualAttribute
{
    /**
     * Constructs an Encryption attribute.
     *
     * @param string $key The encrypter key.
     */
    public function __construct(
        protected string $key = EncryptionManager::DEFAULT
    ) {}

    /**
     * Resolves the Encrypter for contextual injection.
     *
     * @param Container $container The Container.
     * @return Encrypter The Encrypter instance for the encrypter key.
     */
    public function resolve(Container $container): Encrypter
    {
        return $container->use(EncryptionManager::class)->use($this->key);
    }
}

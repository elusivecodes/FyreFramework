<?php
declare(strict_types=1);

namespace Fyre\Auth;

use Fyre\ORM\Entity;

/**
 * Provides persistent user impersonation for an Authenticator.
 */
interface ImpersonationAuthenticatorInterface
{
    /**
     * Starts impersonating a user.
     *
     * @param Entity $impersonator The original user.
     * @param Entity $user The user to impersonate.
     */
    public function impersonate(Entity $impersonator, Entity $user): void;

    /**
     * Returns the original user.
     *
     * @return Entity|null The original user, or null if impersonation is not active.
     */
    public function impersonator(): Entity|null;

    /**
     * Checks whether impersonation is active.
     *
     * @return bool Whether impersonation is active.
     */
    public function isImpersonating(): bool;

    /**
     * Stops impersonating the current user.
     */
    public function stopImpersonating(): void;
}

<?php

namespace Stevebauman\Revision\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Trait AuthenticatedUser
 *
 * @package Stevebauman\Revision
 * @since 1.3.0
 * @version 1.3.0
 * @author Pauljbergmann
 */
trait AuthenticatedUser
{
    /**
     * Get the authenticated user ID.
     *
     * Returns NULL when no user is authenticated.
     *
     * @return int|null
     */
    public function getAuthenticatedUserId(): ?int
    {
        if (Auth::check()) {
            return Auth::id();
        }

        return null;
    }
}

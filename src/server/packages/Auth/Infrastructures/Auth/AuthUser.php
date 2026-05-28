<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Auth;

use Auth\Domain\Models\AuthenticatableAdminUser;
use Illuminate\Contracts\Auth\Authenticatable;

readonly class AuthUser extends AuthenticatableAdminUser implements Authenticatable
{
    public function getAuthIdentifierName()
    {
        return 'adminUserId';
    }

    public function getAuthIdentifier()
    {
        return $this->adminUserId->value;
    }

    public function getAuthPasswordName()
    {
        // Passkey authentication does not use passwords; Laravel's Authenticatable contract still requires this.
        return 'password';
    }

    public function getAuthPassword()
    {
        // Passkey authentication does not use passwords; keep this empty for the framework contract.
        return '';
    }

    public function getRememberToken()
    {
        return '';
    }

    public function setRememberToken($value)
    {
    }

    public function getRememberTokenName()
    {
        return '';
    }
}

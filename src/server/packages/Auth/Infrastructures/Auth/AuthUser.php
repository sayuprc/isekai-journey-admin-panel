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
        return 'password';
    }

    public function getAuthPassword()
    {
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

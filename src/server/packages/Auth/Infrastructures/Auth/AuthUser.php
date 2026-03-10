<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Auth;

use Auth\Domain\Models\AuthenticatableAdminUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Override;

readonly class AuthUser extends AuthenticatableAdminUser implements Authenticatable
{
    #[Override]
    public function getAuthIdentifierName()
    {
        return 'adminUserId';
    }

    #[Override]
    public function getAuthIdentifier()
    {
        return $this->adminUserId->value;
    }

    #[Override]
    public function getAuthPasswordName()
    {
        return 'password';
    }

    #[Override]
    public function getAuthPassword()
    {
        return $this->hashedPassword->value;
    }

    #[Override]
    public function getRememberToken()
    {
        return '';
    }

    #[Override]
    public function setRememberToken($value)
    {
    }

    #[Override]
    public function getRememberTokenName()
    {
        return '';
    }
}

<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Auth;

use AdminUser\Domain\Models\AdminUser;
use Illuminate\Contracts\Auth\Authenticatable;

readonly class AuthUser extends AdminUser implements Authenticatable
{
    public function getAuthIdentifierName()
    {
        return 'userId';
    }

    public function getAuthIdentifier()
    {
        return $this->userId->value;
    }

    public function getAuthPasswordName()
    {
        return 'password';
    }

    public function getAuthPassword()
    {
        return $this->hashedPassword->value;
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

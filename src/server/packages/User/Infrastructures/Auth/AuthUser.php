<?php

declare(strict_types=1);

namespace User\Infrastructures\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use User\Domain\Models\User;

class AuthUser extends User implements Authenticatable
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

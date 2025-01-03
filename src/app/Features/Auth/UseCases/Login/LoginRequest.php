<?php

declare(strict_types=1);

namespace App\Features\Auth\UseCases\Login;

use Exception;

class LoginRequest
{
    /**
     * @param array{email: string, password: string} $credentials
     *
     * @throws Exception
     */
    public function __construct(public readonly array $credentials)
    {
        // @phpstan-ignore-next-line
        if (! array_key_exists('email', $this->credentials) || ! array_key_exists('password', $this->credentials)) {
            throw new Exception('Invalid credentials');
        }
    }
}

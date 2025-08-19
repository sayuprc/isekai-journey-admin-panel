<?php

declare(strict_types=1);

namespace User\Domain\Models\Credential\AccessToken;

readonly class AccessToken
{
    public function __construct(public Jwt $jwt)
    {
    }
}

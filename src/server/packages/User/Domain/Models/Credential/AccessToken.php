<?php

declare(strict_types=1);

namespace User\Domain\Models\Credential;

class AccessToken
{
    public function __construct(public readonly Jwt $jwt)
    {
    }
}

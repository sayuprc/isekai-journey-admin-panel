<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Credential\AccessToken;

use Auth\Domain\Services\Credential\AccessToken\AccessTokenPayload;

interface AccessTokenFactoryInterface
{
    public function create(AccessTokenPayload $payload): AccessToken;
}

<?php

declare(strict_types=1);

namespace User\Domain\Models\Credential;

interface RefreshTokenFactoryInterface
{
    public function create(): RefreshToken;
}

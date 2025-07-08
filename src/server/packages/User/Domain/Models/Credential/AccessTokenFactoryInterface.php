<?php

declare(strict_types=1);

namespace User\Domain\Models\Credential;

interface AccessTokenFactoryInterface
{
    public function create(string $id): AccessToken;
}

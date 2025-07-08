<?php

declare(strict_types=1);

namespace User\Domain\Models\Credential;

interface CredentialFactoryInterface
{
    public function create(string $userId): Credential;
}

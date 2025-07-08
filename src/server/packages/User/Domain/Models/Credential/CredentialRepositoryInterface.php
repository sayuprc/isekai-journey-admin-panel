<?php

declare(strict_types=1);

namespace User\Domain\Models\Credential;

interface CredentialRepositoryInterface
{
    public function insert(Credential $credential): void;
}

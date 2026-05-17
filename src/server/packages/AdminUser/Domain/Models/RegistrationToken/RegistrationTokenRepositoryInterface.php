<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models\RegistrationToken;

interface RegistrationTokenRepositoryInterface
{
    public function save(RegistrationToken $token): RegistrationToken;
}

<?php

declare(strict_types=1);

namespace AdminUser\Domain\Services;

interface RegistrationTokenGeneratorInterface
{
    public function generate(): string;
}

<?php

declare(strict_types=1);

namespace User\Domain\Services\Jwt;

interface JwtConfigInterface
{
    public function issuer(): string;
}

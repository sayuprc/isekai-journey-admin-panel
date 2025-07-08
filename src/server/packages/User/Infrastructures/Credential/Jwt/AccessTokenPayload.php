<?php

declare(strict_types=1);

namespace User\Infrastructures\Credential\Jwt;

use User\Domain\Services\Jwt\Payload;

class AccessTokenPayload implements Payload
{
    public function __construct(
        private readonly string $iss,
        private readonly int $iat,
        private readonly int $exp,
        private readonly int $nbf,
        private readonly string $jti,
    ) {
    }

    public function toArray(): array
    {
        return [
            'iss' => $this->iss,
            'iat' => $this->iat,
            'exp' => $this->exp,
            'nbf' => $this->nbf,
            'jti' => $this->jti,
        ];
    }
}

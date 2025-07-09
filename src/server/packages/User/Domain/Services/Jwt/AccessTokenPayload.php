<?php

declare(strict_types=1);

namespace User\Domain\Services\Jwt;

class AccessTokenPayload
{
    public function __construct(
        public readonly string $iss,
        public readonly int $iat,
        public readonly int $exp,
        public readonly int $nbf,
        public readonly string $jti,
    ) {
    }

    /**
     * @return array{
     *  iss: string,
     *  iat: int,
     *  exp: int,
     *  nbf: int,
     *  jti: string,
     * }
     */
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

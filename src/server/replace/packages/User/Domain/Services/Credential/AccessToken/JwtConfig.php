<?php

declare(strict_types=1);

namespace User\Domain\Services\Credential\AccessToken;

use SensitiveParameter;

readonly class JwtConfig
{
    public function __construct(
        public string $iss,
        public string $alg,
        #[SensitiveParameter] public string $key,
    ) {
    }
}

<?php

declare(strict_types=1);

use User\Domain\Services\Credential\AccessToken\JwtConfig;

use function Tempest\env;

return new JwtConfig(
    iss: env('AUTH_JWT_ISS'),
    alg: env('AUTH_JWT_ALG'),
    key: env('AUTH_JWT_KEY'),
);

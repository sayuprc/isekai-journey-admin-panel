<?php

declare(strict_types=1);

use App\Http\Middleware\OpenApiConfig;

return new OpenApiConfig(path: __DIR__ . '/../../contracts/generated/oas/openapi.yaml');

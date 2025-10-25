<?php

declare(strict_types=1);

namespace App\Http\Middleware;

readonly class OpenApiConfig
{
    public function __construct(public string $path)
    {
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Middleware;

class OpenApiConfig
{
    public function __construct(public readonly string $path)
    {
    }
}

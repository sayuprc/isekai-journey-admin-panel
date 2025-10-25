<?php

declare(strict_types=1);

namespace App\Http\Middleware\Group;

use App\Http\Middleware\OpenApiValidator;
use Attribute;
use Tempest\Http\Method;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\Route;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class Api implements Route
{
    /**
     * @param array<class-string<HttpMiddleware>> $middleware
     * @param array<class-string<HttpMiddleware>> $without
     */
    public function __construct(
        public Method $method,
        public string $uri,
        public array $middleware = [],
        public array $without = [],
    ) {
        $this->uri = $this->buildUri('api', $uri);

        $this->middleware = array_values([
            ...$middleware,
            OpenApiValidator::class,
        ]);
    }

    protected function buildUri(string $prefix, string $uri): string
    {
        return sprintf('/%s/%s', trim($prefix, '/'), trim($uri, '/'));
    }
}

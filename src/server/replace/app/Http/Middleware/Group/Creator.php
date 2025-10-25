<?php

declare(strict_types=1);

namespace App\Http\Middleware\Group;

use Attribute;
use Tempest\Http\Method;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class Creator extends Api
{
    public function __construct(
        public Method $method,
        public string $uri,
        public array $middleware = [],
        public array $without = [],
    ) {
        parent::__construct($method, $this->buildUri('creators', $uri), $middleware, $without);
    }
}

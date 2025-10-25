<?php

declare(strict_types=1);

namespace App\Http\Middleware\Group;

use Attribute;
use Tempest\Http\Method;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class SongType extends Api
{
    public function __construct(
        public Method $method,
        public string $uri,
        public array $middleware = [],
        public array $without = [],
    ) {
        parent::__construct($method, $this->buildUri('song-types', $uri), $middleware, $without);
    }
}

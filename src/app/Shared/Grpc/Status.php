<?php

declare(strict_types=1);

namespace App\Shared\Grpc;

class Status
{
    public function __construct(
        // public readonly array $metadata,
        public readonly int $code,
        public readonly string $details,
    ) {
    }
}

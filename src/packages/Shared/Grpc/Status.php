<?php

declare(strict_types=1);

namespace Shared\Grpc;

use const Grpc\STATUS_OK;

class Status
{
    public function __construct(
        public readonly int $code,
        public readonly string $details,
    ) {
    }

    public function isOk(): bool
    {
        return $this->code === STATUS_OK;
    }
}

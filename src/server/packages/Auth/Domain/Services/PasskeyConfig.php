<?php

declare(strict_types=1);

namespace Auth\Domain\Services;

class PasskeyConfig
{
    public function __construct(
        public readonly string $rpName,
        public readonly string $rpId,
        public readonly string $origin,
        public readonly int $timeoutMs,
        public readonly int $ceremonyTtlSeconds,
        public readonly string $ceremonyCacheStore,
    ) {
    }
}

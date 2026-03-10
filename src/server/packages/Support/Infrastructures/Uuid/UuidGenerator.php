<?php

declare(strict_types=1);

namespace Support\Infrastructures\Uuid;

use Override;
use Illuminate\Support\Str;
use Support\Contracts\Uuid\UuidGeneratorInterface;

readonly class UuidGenerator implements UuidGeneratorInterface
{
    #[Override]
    public function generate(): string
    {
        return Str::uuid()->toString();
    }
}

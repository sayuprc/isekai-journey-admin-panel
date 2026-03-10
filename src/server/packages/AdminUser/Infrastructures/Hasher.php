<?php

declare(strict_types=1);

namespace AdminUser\Infrastructures;

use Override;
use AdminUser\Domain\Services\HasherInterface;
use Illuminate\Support\Facades\Hash;

readonly class Hasher implements HasherInterface
{
    #[Override]
    public function hash(string $plainPassword): string
    {
        return Hash::make($plainPassword);
    }

    #[Override]
    public function check(string $plainPassword, string $hashedPassword): bool
    {
        return Hash::check($plainPassword, $hashedPassword);
    }
}

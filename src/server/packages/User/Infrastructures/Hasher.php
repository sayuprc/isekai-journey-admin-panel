<?php

declare(strict_types=1);

namespace User\Infrastructures;

use Illuminate\Support\Facades\Hash;
use User\Domain\Services\HasherInterface;

readonly class Hasher implements HasherInterface
{
    public function hash(string $plainPassword): string
    {
        return Hash::make($plainPassword);
    }

    public function check(string $plainPassword, string $hashedPassword): bool
    {
        return Hash::check($plainPassword, $hashedPassword);
    }
}

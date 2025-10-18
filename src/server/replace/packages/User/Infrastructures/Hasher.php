<?php

declare(strict_types=1);

namespace User\Infrastructures;

use SensitiveParameter;
use User\Domain\Services\HasherInterface;

readonly class Hasher implements HasherInterface
{
    public function hash(#[SensitiveParameter] string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function check(#[SensitiveParameter] string $plainPassword, string $hashedPassword): bool
    {
        if (strlen($hashedPassword) === 0) {
            return false;
        }

        return password_verify($plainPassword, $hashedPassword);
    }
}

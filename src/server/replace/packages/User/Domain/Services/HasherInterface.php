<?php

declare(strict_types=1);

namespace User\Domain\Services;

use SensitiveParameter;

interface HasherInterface
{
    public function hash(#[SensitiveParameter] string $plainPassword): string;

    public function check(#[SensitiveParameter] string $plainPassword, string $hashedPassword): bool;
}

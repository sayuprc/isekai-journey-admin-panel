<?php

declare(strict_types=1);

namespace User\Domain\Services;

interface HasherInterface
{
    public function hash(string $plainPassword): string;

    public function check(string $plainPassword, string $hashedPassword): bool;
}

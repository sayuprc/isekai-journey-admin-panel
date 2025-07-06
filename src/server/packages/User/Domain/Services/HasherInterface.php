<?php

declare(strict_types=1);

namespace User\Domain\Services;

interface HasherInterface
{
    public function hash(string $plainPassword): string;
}

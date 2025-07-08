<?php

declare(strict_types=1);

namespace User\Domain\Services;

interface RandomTokenGeneratorInterface
{
    public function generate(): string;
}

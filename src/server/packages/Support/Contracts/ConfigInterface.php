<?php

declare(strict_types=1);

namespace Support\Contracts;

interface ConfigInterface
{
    public function getString(string $key): string;
}

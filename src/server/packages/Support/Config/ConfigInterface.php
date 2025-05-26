<?php

declare(strict_types=1);

namespace Support\Config;

interface ConfigInterface
{
    public function getString(string $key, ?string $default = null): string;
}

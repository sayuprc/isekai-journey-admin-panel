<?php

declare(strict_types=1);

namespace Support\Application\Config;

use Exception;
use Support\Config\ConfigInterface;

class Config implements ConfigInterface
{
    /**
     * @throws Exception
     */
    public function getString(string $key, ?string $default = null): string
    {
        $value = $this->get($key, $default);

        if (! is_string($value)) {
            throw new Exception('Config value is not a string');
        }

        return $value;
    }

    private function get(string $key, mixed $default = null): mixed
    {
        return config($key, $default);
    }
}

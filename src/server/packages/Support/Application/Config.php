<?php

declare(strict_types=1);

namespace Support\Application;

use Exception;
use Support\Contracts\ConfigInterface;

class Config implements ConfigInterface
{
    /**
     * @throws Exception
     */
    public function getString(string $key, ?string $default = null): string
    {
        $value = $this->get($key, $default);

        if (! is_string($value)) {
            throw new Exception(sprintf('Config value for key "%s" is not a string', $key));
        }

        return $value;
    }

    private function get(string $key, mixed $default = null): mixed
    {
        return config($key, $default);
    }
}

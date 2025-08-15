<?php

declare(strict_types=1);

namespace Support\Infrastructures;

use Exception;
use Support\Contracts\ConfigInterface;

class Config implements ConfigInterface
{
    /**
     * @throws Exception
     */
    public function getString(string $key): string
    {
        $value = $this->get($key);

        if (! is_string($value)) {
            throw new Exception(sprintf('Config value for key "%s" is not a string', $key));
        }

        return $value;
    }

    /**
     * @throws Exception
     */
    public function getNullableString(string $key, ?string $default = null): ?string
    {
        $value = $this->get($key, $default);

        if (! is_null($value) && ! is_string($value)) {
            throw new Exception(sprintf('Config value for key "%s" is not a string or null', $key));
        }

        return $value;
    }

    /**
     * @throws Exception
     */
    public function getNullableInt(string $key, ?int $default = null): ?int
    {
        $value = $this->get($key, $default);

        if (! is_null($value) && ! is_int($value)) {
            throw new Exception(sprintf('Config value for key "%s" is not a int or null', $key));
        }

        return $value;
    }

    /**
     * @throws Exception
     */
    public function getNullableBool(string $key, ?bool $default = null): ?bool
    {
        $value = $this->get($key, $default);

        if (! is_null($value) && ! is_bool($value)) {
            throw new Exception(sprintf('Config value for key "%s" is not a bool or null', $key));
        }

        return $value;
    }

    private function get(string $key, mixed $default = null): mixed
    {
        return config($key, $default);
    }
}

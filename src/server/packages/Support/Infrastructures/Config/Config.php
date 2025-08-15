<?php

declare(strict_types=1);

namespace Support\Infrastructures\Config;

use Support\Contracts\ConfigInterface;

class Config implements ConfigInterface
{
    /**
     * @throws UnexpectedValueException
     */
    public function getString(string $key): string
    {
        $value = $this->get($key);

        if (! is_string($value)) {
            throw new UnexpectedValueException(sprintf('Config value for key "%s" is not a string', $key));
        }

        return $value;
    }

    /**
     * @throws UnexpectedValueException
     */
    public function getNullableString(string $key, ?string $default = null): ?string
    {
        $value = $this->get($key, $default);

        if (! is_null($value) && ! is_string($value)) {
            throw new UnexpectedValueException(sprintf('Config value for key "%s" is not a string or null', $key));
        }

        return $value;
    }

    /**
     * @throws UnexpectedValueException
     */
    public function getNullableInteger(string $key, ?int $default = null): ?int
    {
        $value = $this->get($key, $default);

        if (! is_null($value) && ! is_int($value)) {
            throw new UnexpectedValueException(sprintf('Config value for key "%s" is not an integer or null', $key));
        }

        return $value;
    }

    /**
     * @throws UnexpectedValueException
     */
    public function getNullableBoolean(string $key, ?bool $default = null): ?bool
    {
        $value = $this->get($key, $default);

        if (! is_null($value) && ! is_bool($value)) {
            throw new UnexpectedValueException(sprintf('Config value for key "%s" is not a boolean or null', $key));
        }

        return $value;
    }

    private function get(string $key, mixed $default = null): mixed
    {
        return config($key, $default);
    }
}

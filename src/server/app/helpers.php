<?php

declare(strict_types=1);

if (! function_exists('nullableBool')) {
    function nullableBool(string $key): ?bool
    {
        $value = config($key);

        if (is_null($value) || is_bool($value)) {
            return $value;
        }

        throw new \Exception(sprintf('%s は bool もしくは null である必要があります。', $key));
    }
}

if (! function_exists('nullableInt')) {
    function nullableInt(string $key): ?int
    {
        $value = config($key);

        if (is_null($value) || is_int($value)) {
            return $value;
        }

        throw new \Exception(sprintf('%s は int もしくは null である必要があります。', $key));
    }
}

if (! function_exists('nullableString')) {
    function nullableString(string $key): ?string
    {
        $value = config($key);

        if (is_null($value) || is_string($value)) {
            return $value;
        }

        throw new \Exception(sprintf('%s は string もしくは null である必要があります。', $key));
    }
}

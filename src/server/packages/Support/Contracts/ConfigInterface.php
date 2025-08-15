<?php

declare(strict_types=1);

namespace Support\Contracts;

interface ConfigInterface
{
    public function getString(string $key): string;

    public function getNullableString(string $key, ?string $default = null): ?string;

    public function getNullableInteger(string $key, ?int $default = null): ?int;

    public function getNullableBoolean(string $key, ?bool $default = null): ?bool;
}

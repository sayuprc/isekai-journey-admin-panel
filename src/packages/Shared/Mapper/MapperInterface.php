<?php

declare(strict_types=1);

namespace Shared\Mapper;

interface MapperInterface
{
    /**
     * @template T
     *
     * @param string|class-string<T> $signature
     * @param mixed                  $source
     *
     * @return T
     *
     * @phpstan-return (
     *     $signature is class-string<T>
     *         ? T
     *         : ($signature is class-string ? object : mixed)
     * )
     */
    public function map(string $signature, mixed $source): mixed;
}

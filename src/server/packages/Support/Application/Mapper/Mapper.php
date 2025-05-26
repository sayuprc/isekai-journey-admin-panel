<?php

declare(strict_types=1);

namespace Support\Application\Mapper;

use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use Support\Mapper\MapperInterface;

class Mapper implements MapperInterface
{
    public function __construct(private readonly MapperBuilder $builder)
    {
    }

    public function map(string $signature, mixed $source): mixed
    {
        $json = ! is_string($source) || ! json_validate($source)
            ? (string)json_encode($source)
            : $source;

        return $this->builder
            ->allowSuperfluousKeys()
            ->enableFlexibleCasting()
            ->supportDateFormats('Y-m-d')
            ->mapper()
            ->map($signature, Source::json($json)->camelCaseKeys());
    }
}

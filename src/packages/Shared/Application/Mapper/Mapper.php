<?php

declare(strict_types=1);

namespace Shared\Application\Mapper;

use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use Shared\Mapper\MapperInterface;

class Mapper implements MapperInterface
{
    public function __construct(private readonly MapperBuilder $builder)
    {
    }

    public function mapFromArray(string $signature, mixed $source): mixed
    {
        $source = is_array($source) ? $source : [$source];

        return $this->builder
            ->allowSuperfluousKeys()
            ->enableFlexibleCasting()
            ->mapper()
            ->map($signature, Source::array($source)->camelCaseKeys());
    }

    public function mapFromJson(string $signature, mixed $source): mixed
    {
        $json = (string)json_encode($source);

        return $this->builder
            ->allowSuperfluousKeys()
            ->mapper()
            ->map($signature, Source::json($json)->camelCaseKeys());
    }
}

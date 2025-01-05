<?php

declare(strict_types=1);

namespace App\Shared\Application\Mapper;

use App\Shared\Mapper\MapperInterface;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;

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

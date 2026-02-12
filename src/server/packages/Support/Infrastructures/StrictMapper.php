<?php

declare(strict_types=1);

namespace Support\Infrastructures;

use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use DateTimeInterface;
use Support\Contracts\MapperInterface;

readonly class StrictMapper implements MapperInterface
{
    public function __construct(private MapperBuilder $builder)
    {
    }

    public function map(string $signature, mixed $source): mixed
    {
        if (is_array($source)) {
            $source = Source::iterable($source);
        } elseif (is_string($source) && json_validate($source)) {
            $source = Source::json($source);
        } else {
            // Assume iterable
            /** @var iterable<mixed> $source */
            $source = Source::iterable($source);
        }

        return $this->builder
            ->allowSuperfluousKeys()
            ->supportDateFormats('Y-m-d', 'Y-m-d H:i:s', DateTimeInterface::ATOM)
            ->mapper()
            ->map($signature, $source);
    }
}

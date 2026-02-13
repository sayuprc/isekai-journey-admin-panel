<?php

declare(strict_types=1);

namespace Support\Infrastructures;

use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use DateTimeImmutable;
use DateTimeInterface;
use DateType\ImmutableDate;
use Support\Contracts\MapperInterface;
use Support\Domain\ValueObjects\Date\ImmutableDateTimeValueObject;
use Support\Domain\ValueObjects\Date\ImmutableDateValueObject;
use Support\Domain\ValueObjects\Numeric\IntegerValueObject;
use Support\Domain\ValueObjects\String\StringValueObject;

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
            assert(is_iterable($source));
            $source = Source::iterable($source);
        }

        return $this->builder
            ->allowSuperfluousKeys()
            ->supportDateFormats('Y-m-d', 'Y-m-d H:i:s', DateTimeInterface::ATOM)
            ->registerConstructor(
                fn (string $value): StringValueObject => StringValueObject::reconstruct($value),
                fn (int $value): IntegerValueObject => IntegerValueObject::reconstruct($value),
                fn (ImmutableDate $value): ImmutableDateValueObject => ImmutableDateValueObject::reconstruct($value),
                fn (DateTimeImmutable $value): ImmutableDateTimeValueObject => ImmutableDateTimeValueObject::reconstruct($value),
            )
            ->mapper()
            ->map($signature, $source);
    }
}

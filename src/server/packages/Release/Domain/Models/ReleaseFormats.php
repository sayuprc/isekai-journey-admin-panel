<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Collection\ImmutableCollection;
use Support\Domain\Error\DomainValidationError;

/**
 * @extends ImmutableCollection<int, ReleaseFormat>
 */
readonly class ReleaseFormats extends ImmutableCollection
{
    /**
     * @param list<int> $values
     *
     * @return Result<self, DomainValidationError>
     */
    public static function fromArray(array $values): Result
    {
        if ($values === []) {
            return new Err(new DomainValidationError([
                'formatValues' => ['提供形態は 1 つ以上指定してください。'],
            ]));
        }

        $formats = [];
        $seenValues = [];

        foreach ($values as $value) {
            $format = ReleaseFormat::tryFrom($value);

            if (is_null($format)) {
                return new Err(new DomainValidationError([
                    'formatValues' => ["不正な提供形態です: {$value}"],
                ]));
            }

            if (isset($seenValues[$format->value])) {
                return new Err(new DomainValidationError([
                    'formatValues' => ['同じ提供形態を複数指定することはできません。'],
                ]));
            }

            $seenValues[$format->value] = true;
            $formats[] = $format;
        }

        return new Ok(new self($formats));
    }

    /**
     * @param list<int> $values
     */
    public static function reconstruct(array $values): self
    {
        return new self(array_map(
            static fn (int $value): ReleaseFormat => ReleaseFormat::from($value),
            $values,
        ));
    }

    /**
     * @return list<value-of<ReleaseFormat>>
     */
    public function toArray(): array
    {
        $values = [];

        foreach ($this->toGeneric() as $format) {
            $values[] = $format->value;
        }

        return $values;
    }
}

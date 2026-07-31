<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\DomainValidationException;

/**
 * @extends ImmutableCollection<int, ReleaseFormat>
 */
readonly class ReleaseFormats extends ImmutableCollection
{
    /**
     * @param list<int> $values
     *
     * @throws DomainValidationException
     */
    public static function fromArray(array $values): self
    {
        if ($values === []) {
            throw new DomainValidationException([
                'formatValues' => ['提供形態は 1 つ以上指定してください。'],
            ]);
        }

        $formats = [];
        $seenValues = [];

        foreach ($values as $value) {
            $format = ReleaseFormat::tryFrom($value);

            if (is_null($format)) {
                throw new DomainValidationException([
                    'formatValues' => ["不正な提供形態です: {$value}"],
                ]);
            }

            if (isset($seenValues[$format->value])) {
                throw new DomainValidationException([
                    'formatValues' => ['同じ提供形態を複数指定することはできません。'],
                ]);
            }

            $seenValues[$format->value] = true;
            $formats[] = $format;
        }

        return new self($formats);
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

<?php

declare(strict_types=1);

namespace Creator\Infrastructures;

use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use ResultType\Result;
use Support\Contracts\UuidGeneratorInterface;

readonly class CreatorFactory implements CreatorFactoryInterface
{
    public function __construct(private UuidGeneratorInterface $uuid)
    {
    }

    public function create(string $creatorName): Result
    {
        return Result::collect(
            CreatorId::create($this->uuid->generate()),
            CreatorName::create($creatorName),
        )->map(fn (array $values): Creator => new Creator(...$values))
            ->mapErr(fn (array $errors): array => array_filter($errors, fn ($item) => ! is_null($item)));
    }

    public function reconstitute(string $creatorId, string $creatorName): Result
    {
        return Result::collect(
            CreatorId::create($creatorId),
            CreatorName::create($creatorName),
        )->map(fn (array $values): Creator => new Creator(...$values))
            ->mapErr(fn (array $errors): array => array_filter($errors, fn ($item) => ! is_null($item)));
    }
}

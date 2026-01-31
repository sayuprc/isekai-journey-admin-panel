<?php

declare(strict_types=1);

namespace Creator\Domain\Services;

use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Models\CreatorRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\UuidGeneratorInterface;

/**
 * TODO エラーハンドリングを強化する
 */
class CreatorIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly CreatorFactoryInterface $factory,
        private readonly CreatorRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<Creator, string>
     */
    public function prepareForCreate(string $creatorName): Result
    {
        $result = $this->build($this->generator->generate(), $creatorName);

        if ($result->isErr()) {
            return new Err('');
        }

        $creator = $result->unwrap();

        if (! is_null($this->repository->findByName($creator->creatorName))) {
            return new Err(sprintf('すでに使われている名前です "%s"', $creatorName));
        }

        return new Ok($creator);
    }

    /**
     * @return Result<Creator, string>
     */
    public function prepareForUpdate(string $creatorId, string $creatorName): Result
    {
        $result = $this->build($creatorId, $creatorName);

        if ($result->isErr()) {
            return new Err('');
        }

        $creator = $result->unwrap();

        if (
            ! is_null($found = $this->repository->findByName($creator->creatorName))
            && $found->creatorId->value !== $creator->creatorId->value
        ) {
            return new Err(sprintf('すでに使われている名前です "%s"', $creatorName));
        }

        return new Ok($creator);
    }

    /**
     * @return Result<Creator, string>
     */
    private function build(string $creatorId, string $creatorName): Result
    {
        return Result::collect(
            CreatorId::create($creatorId),
            CreatorName::create($creatorName),
        )
            ->mapErr(fn (): string => '')
            ->map(fn (array $values): Creator => $this->factory->create(...$values));
    }
}

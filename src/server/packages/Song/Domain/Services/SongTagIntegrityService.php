<?php

declare(strict_types=1);

namespace Song\Domain\Services;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagFactoryInterface;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;

class SongTagIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly SongTagFactoryInterface $factory,
        private readonly SongTagRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<SongTag, DomainError>
     */
    public function prepareForCreate(string $name): Result
    {
        $result = $this->build(
            $this->generator->generate(),
            $name,
            $this->repository->getMaxOrderNo() + 10,
        );

        if ($result->isErr()) {
            return new Err($result->unwrapErr());
        }

        $tag = $result->unwrap();

        if (! is_null($this->repository->findByName($tag->name))) {
            return new Err(new BusinessRuleViolationError(sprintf('すでに使われているタグ名です "%s"', $name)));
        }

        return new Ok($tag);
    }

    /**
     * @return Result<SongTag, DomainError>
     */
    private function build(string $songTagId, string $name, int $orderNo): Result
    {
        return Result::collect3(
            SongTagId::create($songTagId),
            SongTagName::create($name),
            OrderNo::create($orderNo),
        )
            ->mapErr(function (array $errors): DomainValidationError {
                $messages = [];
                foreach ($errors as $error) {
                    if ($error instanceof EntityRuleViolationError) {
                        $messages[$error->field] ??= [];
                        $messages[$error->field][] = $error->message;
                    }
                }

                return new DomainValidationError($messages);
            })
            ->map(fn (array $values): SongTag => $this->factory->create(...$values));
    }
}

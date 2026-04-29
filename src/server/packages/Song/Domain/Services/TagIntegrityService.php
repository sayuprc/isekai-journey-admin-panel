<?php

declare(strict_types=1);

namespace Song\Domain\Services;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\Tag;
use Song\Domain\Models\TagFactoryInterface;
use Song\Domain\Models\TagId;
use Song\Domain\Models\TagName;
use Song\Domain\Models\TagRepositoryInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;

class TagIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly TagFactoryInterface $factory,
        private readonly TagRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<Tag, DomainError>
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
            return new Err(new BusinessRuleViolationError(sprintf('すでに使われている名前です "%s"', $name)));
        }

        return new Ok($tag);
    }

    /**
     * @return Result<Tag, DomainError>
     */
    public function prepareForUpdate(string $tagId, string $name, int $orderNo): Result
    {
        $result = $this->build($tagId, $name, $orderNo);

        if ($result->isErr()) {
            return new Err($result->unwrapErr());
        }

        $tag = $result->unwrap();

        if (! is_null($found = $this->repository->findByName($tag->name)) && ! $found->equals($tag)) {
            return new Err(new BusinessRuleViolationError(sprintf('すでに使われている名前です "%s"', $name)));
        }

        return new Ok($tag);
    }

    /**
     * @return Result<Tag, DomainError>
     */
    private function build(string $tagId, string $name, int $orderNo): Result
    {
        return Result::collect3(
            TagId::create($tagId),
            TagName::create($name),
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
            ->map(fn (array $values): Tag => $this->factory->create(...$values));
    }
}

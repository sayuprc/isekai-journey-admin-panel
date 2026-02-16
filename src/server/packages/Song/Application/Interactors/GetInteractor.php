<?php

declare(strict_types=1);

namespace Song\Application\Interactors;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\Assemble\SongAssembler;
use Song\Application\UseCase\Get\GetInputData;
use Song\Application\UseCase\Get\GetOutputData;
use Song\Application\UseCase\Get\GetUseCaseInterface;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Support\Domain\Error\DomainRuleViolationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

readonly class GetInteractor implements GetUseCaseInterface
{
    public function __construct(
        private SongRepositoryInterface $repository,
        private SongAssembler $assembler,
    ) {
    }

    public function handle(GetInputData $inputData): Result
    {
        return SongId::create($inputData->songId)
            ->mapErr(fn (DomainRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(function (SongId $songId): Result {
                if (is_null($found = $this->repository->find($songId))) {
                    return new Err(new NotFoundError('楽曲', $songId->value));
                }

                return new Ok(new GetOutputData($this->assembler->assemble($found)));
            });
    }
}

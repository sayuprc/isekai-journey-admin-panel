<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Get;

use AdminUser\Domain\Models\Permission;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\Assemble\SongAssembler;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private SongRepositoryInterface $repository,
        private SongAssembler $assembler,
    ) {
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    public function handle(GetInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::ReadSong)
            ->andThen(fn () => $this->getSong($inputData));
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    private function getSong(GetInputData $inputData): Result
    {
        return SongId::create($inputData->songId)
            ->mapErr(fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(function (SongId $songId): Result {
                if (is_null($found = $this->repository->find($songId))) {
                    return new Err(new NotFoundError('楽曲', $songId->value));
                }

                return new Ok(new GetOutputData($this->assembler->assemble($found)));
            });
    }
}

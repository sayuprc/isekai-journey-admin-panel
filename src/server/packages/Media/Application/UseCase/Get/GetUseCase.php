<?php

declare(strict_types=1);

namespace Media\Application\UseCase\Get;

use AdminUser\Domain\Models\Permission;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private MediaRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    public function handle(GetInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::ReadMedia)
            ->andThen(fn () => $this->getMedia($inputData));
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    private function getMedia(GetInputData $inputData): Result
    {
        return MediaId::create($inputData->mediaId)
            ->mapErr(fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(function (MediaId $mediaId): Result {
                if (is_null($found = $this->repository->find($mediaId))) {
                    return new Err(new NotFoundError('Media', $mediaId->value));
                }

                return new Ok(new GetOutputData($found));
            });
    }
}

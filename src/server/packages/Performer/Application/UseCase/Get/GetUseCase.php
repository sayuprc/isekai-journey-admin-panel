<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\Get;

use AdminUser\Domain\Models\Permission;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerRepositoryInterface;
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
        private PerformerRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    public function handle(GetInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::ReadPerformer)
            ->andThen(fn () => $this->getPerformer($inputData));
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    private function getPerformer(GetInputData $inputData): Result
    {
        return PerformerId::create($inputData->performerId)
            ->mapErr(fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(function (PerformerId $performerId): Result {
                if (is_null($found = $this->repository->find($performerId))) {
                    return new Err(new NotFoundError('Performer', $performerId->value));
                }

                return new Ok(new GetOutputData($found));
            });
    }
}

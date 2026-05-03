<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerRepositoryInterface;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private PerformerRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<null, UseCaseError>
     */
    public function handle(DeleteInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WritePerformer)
            ->andThen(fn () => $this->deletePerformer($inputData));
    }

    /**
     * @return Result<null, UseCaseError>
     */
    private function deletePerformer(DeleteInputData $inputData): Result
    {
        return PerformerId::create($inputData->performerId)
            ->mapErr(fn (): UseCaseError => new InvalidInputError(['performerId' => ['IDが不正です']]))
            ->andThen(function (PerformerId $performerId): Result {
                $this->repository->delete($performerId);

                return new Ok(null);
            });
    }
}

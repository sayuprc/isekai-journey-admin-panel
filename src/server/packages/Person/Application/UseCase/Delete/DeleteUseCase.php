<?php

declare(strict_types=1);

namespace Person\Application\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonRepositoryInterface;
use Person\Domain\Services\PersonUsageCheckerInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private PersonRepositoryInterface $repository,
        private PersonUsageCheckerInterface $usageChecker,
    ) {
    }

    /**
     * @return Result<null, UseCaseError>
     */
    public function handle(DeleteInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WritePerson)
            ->andThen(fn () => $this->deletePerson($inputData));
    }

    /**
     * @return Result<null, UseCaseError>
     */
    private function deletePerson(DeleteInputData $inputData): Result
    {
        return PersonId::create($inputData->personId)
            ->mapErr(fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(function (PersonId $personId): Result {
                if ($this->usageChecker->isUsed($personId)) {
                    return new Err(new BusinessLogicError('この人物は楽曲に使用されているため削除できません'));
                }

                $this->repository->delete($personId);

                return new Ok(null);
            });
    }
}

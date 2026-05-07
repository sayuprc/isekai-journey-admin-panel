<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Get;

use AdminUser\Domain\Models\Permission;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonRepositoryInterface;
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
        private PersonRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    public function handle(GetInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::ReadPerson)
            ->andThen(fn () => $this->getPerson($inputData));
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    private function getPerson(GetInputData $inputData): Result
    {
        return PersonId::create($inputData->personId)
            ->mapErr(fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(function (PersonId $personId): Result {
                if (is_null($found = $this->repository->find($personId))) {
                    return new Err(new NotFoundError('Person', $personId->value));
                }

                return new Ok(new GetOutputData($found));
            });
    }
}

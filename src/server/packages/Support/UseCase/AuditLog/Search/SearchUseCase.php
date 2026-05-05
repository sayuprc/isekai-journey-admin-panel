<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog\Search;

use AdminUser\Domain\Models\Permission;
use ResultType\Ok;
use ResultType\Result;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\AuditLog\Query\AuditLogQueryServiceInterface;
use Support\UseCase\AuditLog\Query\AuditLogSearchCriteria;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\UseCaseError;

readonly class SearchUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private AuditLogQueryServiceInterface $query,
    ) {
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    public function handle(SearchInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::ReadAuditLog)
            ->andThen(fn () => $this->searchAuditLogs($inputData));
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    private function searchAuditLogs(SearchInputData $inputData): Result
    {
        $criteria = new AuditLogSearchCriteria(
            $inputData->from === Arg::Optional ? new None() : new Some($inputData->from),
            $inputData->to === Arg::Optional ? new None() : new Some($inputData->to),
            $inputData->action === Arg::Optional ? new None() : new Some($inputData->action),
            $inputData->targetType === Arg::Optional ? new None() : new Some($inputData->targetType),
            $inputData->targetId === Arg::Optional ? new None() : new Some($inputData->targetId),
            $inputData->adminUserName === Arg::Optional ? new None() : new Some($inputData->adminUserName),
            $inputData->page,
            $inputData->perPage,
        );

        return new Ok(
            new SearchOutputData(
                $this->query->search($criteria),
                $this->query->maxPage($criteria),
            ),
        );
    }
}

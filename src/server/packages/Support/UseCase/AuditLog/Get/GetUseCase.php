<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog\Get;

use AdminUser\Domain\Models\Permission;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\AuditLog\Query\AuditLogQueryServiceInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private AuditLogQueryServiceInterface $query,
    ) {
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    public function handle(GetInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::ReadAuditLog)
            ->andThen(fn () => $this->getAuditLog($inputData));
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    private function getAuditLog(GetInputData $inputData): Result
    {
        $found = $this->query->find($inputData->auditLogId);

        if (is_null($found)) {
            return new Err(new NotFoundError('監査ログ', $inputData->auditLogId));
        }

        return new Ok(new GetOutputData($found));
    }
}

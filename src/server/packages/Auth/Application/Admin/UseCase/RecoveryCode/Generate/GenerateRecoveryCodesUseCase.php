<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\RecoveryCode\Generate;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use Auth\Domain\Models\AuthContext;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeRepositoryInterface;
use Auth\Domain\Services\RecoveryCode\RecoveryCodeIssueService;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class GenerateRecoveryCodesUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private AuthContext $authContext,
        private RecoveryCodeIssueService $issueService,
        private RecoveryCodeRepositoryInterface $repository,
        private AdminUserRepositoryInterface $adminUserRepository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<GenerateRecoveryCodesOutputData, UseCaseError>
     */
    public function handle(): Result
    {
        $adminUser = $this->authContext->get();

        if (is_null($adminUser)) {
            return new Err(new AuthenticationError());
        }

        $adminUserId = $adminUser->adminUserId;

        return $this->transaction->scope(function () use ($adminUserId): Result {
            // 同一ユーザーの同時発行を直列化する。所有者行を FOR UPDATE でロックし、
            // delete -> insert を他リクエストと交錯させない (両方のコードが残る・デッドロックを防ぐ)。
            $this->adminUserRepository->findByIdForUpdate($adminUserId);

            $issueResult = $this->issueService->issue($adminUserId);

            if ($issueResult->isErr()) {
                return new Err($this->handleError($issueResult->unwrapErr()));
            }

            ['codes' => $codes, 'plainCodes' => $plainCodes] = $issueResult->unwrap();

            $this->repository->deleteByAdminUserId($adminUserId);
            $this->repository->saveMany($codes);

            $this->recorder->record(
                AuditAction::RecoveryCodeIssue,
                AuditTargetType::AdminUser,
                $adminUserId,
                ['count' => count($codes)],
                $adminUserId,
            );

            return new Ok(new GenerateRecoveryCodesOutputData($plainCodes));
        });
    }

    private function handleError(DomainError $error): UseCaseError
    {
        return match (true) {
            $error instanceof DomainValidationError => new InvalidInputError($error->errors),
            $error instanceof EntityRuleViolationError => new InvalidInputError([$error->field => [$error->message]]),
            default => throw new LogicException('予期しないドメインエラーが発生しました: ' . $error::class),
        };
    }
}

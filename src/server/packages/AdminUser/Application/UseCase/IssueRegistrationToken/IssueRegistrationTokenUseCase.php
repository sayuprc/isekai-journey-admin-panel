<?php

declare(strict_types=1);

namespace AdminUser\Application\UseCase\IssueRegistrationToken;

use AdminUser\Domain\Models\AdminUserRegistrationTokenRepositoryInterface;
use AdminUser\Domain\Services\RegistrationTokenIssueService;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class IssueRegistrationTokenUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private RegistrationTokenIssueService $service,
        private AdminUserRegistrationTokenRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<IssueRegistrationTokenOutputData, UseCaseError>
     */
    public function handle(IssueRegistrationTokenInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->issue(
                $inputData->name,
                $inputData->email,
                $inputData->role,
                $inputData->expiresInMinutes,
            );

            if ($result->isErr()) {
                return new Err($this->handleError($result->unwrapErr()));
            }

            ['token' => $token, 'plainToken' => $plainToken] = $result->unwrap();

            $this->repository->save($token);

            return new Ok(new IssueRegistrationTokenOutputData($token, $plainToken));
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

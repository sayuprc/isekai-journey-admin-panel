<?php

declare(strict_types=1);

namespace AdminUser\Application\Cli\UseCase\IssueRegistrationToken;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use AdminUser\Domain\Services\RegistrationToken\RegistrationTokenIssueService;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class IssueRegistrationTokenUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private AdminUserRepositoryInterface $adminUserRepository,
        private RegistrationTokenIssueService $issueService,
        private RegistrationTokenRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<IssueRegistrationTokenOutputData, UseCaseError>
     */
    public function handle(IssueRegistrationTokenInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $emailResult = Email::create($inputData->email);

            if ($emailResult->isErr()) {
                return new Err($this->handleError($emailResult->unwrapErr()));
            }

            $email = $emailResult->unwrap();

            if (! is_null($this->adminUserRepository->findByEmail($email))) {
                return new Err($this->handleError(new BusinessRuleViolationError(
                    sprintf('すでに使われているメールアドレスです "%s"', $inputData->email),
                )));
            }

            $issueResult = $this->issueService->issue($email, $inputData->role, $inputData->permissions);

            if ($issueResult->isErr()) {
                return new Err($this->handleError($issueResult->unwrapErr()));
            }

            $issued = $issueResult->unwrap();

            $token = $this->repository->save($issued['token']);

            return new Ok(new IssueRegistrationTokenOutputData($token, $issued['plainToken']));
        });
    }

    private function handleError(DomainError $error): UseCaseError
    {
        return match (true) {
            $error instanceof DomainValidationError => new InvalidInputError($error->errors),
            $error instanceof EntityRuleViolationError => new InvalidInputError([$error->field => [$error->message]]),
            $error instanceof BusinessRuleViolationError => new BusinessLogicError($error->message),
            default => throw new LogicException('予期しないドメインエラーが発生しました: ' . $error::class),
        };
    }
}

<?php

declare(strict_types=1);

namespace AdminUser\Application\Cli\UseCase\RevokeRegistrationToken;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
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

readonly class RevokeRegistrationTokenUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private RegistrationTokenRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<RevokeRegistrationTokenOutputData, UseCaseError>
     */
    public function handle(RevokeRegistrationTokenInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $emailResult = Email::create($inputData->email);

            if ($emailResult->isErr()) {
                return new Err($this->handleError($emailResult->unwrapErr()));
            }

            $revokedCount = $this->repository->revokeUnusedByEmail($emailResult->unwrap());

            return new Ok(new RevokeRegistrationTokenOutputData($revokedCount));
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

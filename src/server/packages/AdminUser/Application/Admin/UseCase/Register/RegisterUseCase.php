<?php

declare(strict_types=1);

namespace AdminUser\Application\Admin\UseCase\Register;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Models\Invitation\InvitationRepositoryInterface;
use AdminUser\Domain\Models\Invitation\PlainToken;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use AdminUser\Domain\Services\HasherInterface;
use AdminUser\Domain\Services\TokenHasherInterface;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class RegisterUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private ClockInterface $clock,
        private HasherInterface $passwordHasher,
        private TokenHasherInterface $tokenHasher,
        private InvitationRepositoryInterface $invitationRepository,
        private AdminUserRepositoryInterface $adminUserRepository,
        private AdminUserIntegrityService $integrityService,
    ) {
    }

    /**
     * @return Result<RegisterOutputData, UseCaseError>
     */
    public function handle(RegisterInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $tokenResult = PlainToken::create($inputData->token);
            if ($tokenResult->isErr()) {
                return new Err(new AuthenticationError());
            }

            $hashedToken = $this->tokenHasher->hash($tokenResult->unwrap());

            $invitation = $this->invitationRepository->findByHashedToken($hashedToken);
            if (is_null($invitation)) {
                return new Err(new AuthenticationError());
            }

            $consumeResult = $invitation->consume($this->clock->now());
            if ($consumeResult->isErr()) {
                return new Err(new AuthenticationError());
            }

            $consumedInvitation = $consumeResult->unwrap();

            $adminUserResult = $this->integrityService->prepareForCreate(
                $inputData->name,
                $inputData->email,
                $consumedInvitation->role->value,
                $consumedInvitation->permissions->toArray(),
            );

            if ($adminUserResult->isErr()) {
                return new Err($this->handleError($adminUserResult->unwrapErr()));
            }

            $passwordResult = HashedPassword::create($this->passwordHasher->hash($inputData->plainPassword));
            if ($passwordResult->isErr()) {
                return new Err($this->handleError($passwordResult->unwrapErr()));
            }

            $adminUser = $this->adminUserRepository->register($adminUserResult->unwrap(), $passwordResult->unwrap());

            $this->invitationRepository->save($consumedInvitation);

            return new Ok(new RegisterOutputData($adminUser));
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

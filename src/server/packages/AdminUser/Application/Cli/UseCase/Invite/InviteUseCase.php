<?php

declare(strict_types=1);

namespace AdminUser\Application\Cli\UseCase\Invite;

use AdminUser\Domain\Models\Invitation\ExpiresAt;
use AdminUser\Domain\Models\Invitation\Invitation;
use AdminUser\Domain\Models\Invitation\InvitationId;
use AdminUser\Domain\Models\Invitation\InvitationRepositoryInterface;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\PlainTokenGeneratorInterface;
use AdminUser\Domain\Services\TokenHasherInterface;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Support\Contracts\TransactionInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class InviteUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private ClockInterface $clock,
        private UuidGeneratorInterface $uuidGenerator,
        private PlainTokenGeneratorInterface $tokenGenerator,
        private TokenHasherInterface $tokenHasher,
        private InvitationRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<InviteOutputData, UseCaseError>
     */
    public function handle(InviteInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $role = Role::tryFrom($inputData->role);
            if (is_null($role)) {
                return new Err(new InvalidInputError([
                    'role' => ["不正なロールです: {$inputData->role}"],
                ]));
            }

            $permissionsResult = Permissions::fromArray($inputData->permissions);
            if ($permissionsResult->isErr()) {
                return new Err($this->handleError($permissionsResult->unwrapErr()));
            }

            $now = $this->clock->now();
            $expiresAt = ExpiresAt::reconstruct($now->modify("+{$inputData->expiresInHours} hours"));

            $plainToken = $this->tokenGenerator->generate();
            $hashedToken = $this->tokenHasher->hash($plainToken);

            $invitation = new Invitation(
                InvitationId::reconstruct($this->uuidGenerator->generate()),
                $hashedToken,
                $role,
                $permissionsResult->unwrap(),
                $expiresAt,
                null,
            );

            $this->repository->save($invitation);

            return new Ok(new InviteOutputData($plainToken, $expiresAt));
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

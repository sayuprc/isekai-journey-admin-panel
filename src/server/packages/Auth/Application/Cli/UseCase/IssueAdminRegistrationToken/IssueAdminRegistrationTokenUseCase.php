<?php

declare(strict_types=1);

namespace Auth\Application\Cli\UseCase\IssueAdminRegistrationToken;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AdminRegistrationToken\AdminRegistrationToken;
use Auth\Domain\Models\AdminRegistrationToken\AdminRegistrationTokenId;
use Auth\Domain\Models\AdminRegistrationToken\AdminRegistrationTokenRepositoryInterface;
use Auth\Domain\Models\AdminRegistrationToken\ConsumptionStatus;
use Auth\Domain\Models\AdminRegistrationToken\ExpiredAt;
use Auth\Domain\Models\AdminRegistrationToken\HashedTokenValue;
use Auth\Domain\Services\Token\RefreshToken\RandomTokenGeneratorInterface;
use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
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

readonly class IssueAdminRegistrationTokenUseCase
{
    private const int TTL_HOUR = 24;

    public function __construct(
        private TransactionInterface $transaction,
        private AdminRegistrationTokenRepositoryInterface $repository,
        private ClockInterface $clock,
        private UuidGeneratorInterface $uuidGenerator,
        private RandomTokenGeneratorInterface $randomTokenGenerator,
        private TokenHasherInterface $tokenHasher,
    ) {
    }

    /**
     * @return Result<IssueAdminRegistrationTokenOutputData, UseCaseError>
     */
    public function handle(IssueAdminRegistrationTokenInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $plainToken = $this->randomTokenGenerator->generate();
            $hashedToken = $this->tokenHasher->hash($plainToken);

            $result = Result::collect5(
                AdminRegistrationTokenId::create($this->uuidGenerator->generate()),
                Email::create($inputData->email),
                $this->toRole($inputData->role),
                HashedTokenValue::create($hashedToken),
                ExpiredAt::create($this->clock->now()->modify('+' . self::TTL_HOUR . ' hours')),
            )->map(fn (array $values): AdminRegistrationToken => new AdminRegistrationToken(...[...$values, ConsumptionStatus::Unused]));

            if ($result->isErr()) {
                return new Err($this->handleError($result->unwrapErr()));
            }

            $this->repository->save($result->unwrap());

            return new Ok(new IssueAdminRegistrationTokenOutputData($plainToken));
        });
    }

    /**
     * @param array<int, DomainError|null> $errors
     */
    private function handleError(array $errors): UseCaseError
    {
        $messages = [];
        foreach ($errors as $error) {
            if (! $error instanceof DomainError) {
                continue;
            }

            if ($error instanceof EntityRuleViolationError) {
                $messages[$error->field] ??= [];
                $messages[$error->field][] = $error->message;
            }
        }

        if ($messages !== []) {
            return new InvalidInputError($messages);
        }

        throw new LogicException('予期しないドメインエラーが発生しました: ' . DomainValidationError::class);
    }

    /**
     * @return Result<Role, DomainError>
     */
    private function toRole(int $role): Result
    {
        $result = Role::tryFrom($role);

        if (! is_null($result)) {
            return new Ok($result);
        }

        return new Err(new EntityRuleViolationError(Role::class, "不正なロールです: {$role}"));
    }
}

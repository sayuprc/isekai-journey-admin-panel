<?php

declare(strict_types=1);

namespace AdminUser\Application\Service;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use AdminUser\Domain\Services\HasherInterface;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class RegisterAdminUserService
{
    public function __construct(
        private HasherInterface $hasher,
        private AdminUserRepositoryInterface $repository,
        private AdminUserIntegrityService $service,
    ) {
    }

    /**
     * @param list<string> $permissions
     *
     * @return Result<AdminUser, UseCaseError>
     */
    public function handle(
        string $name,
        string $email,
        string $plainPassword,
        int $role,
        array $permissions,
    ): Result {
        $errors = [];

        if (mb_trim($name) === '') {
            $errors['name'] = ['管理ユーザー名を入力してください'];
        }

        if (mb_trim($email) === '') {
            $errors['email'] = ['メールアドレスを入力してください'];
        }

        if (mb_trim($plainPassword) === '') {
            $errors['password'] = ['パスワードを入力してください'];
        }

        if ($errors !== []) {
            return new Err(new InvalidInputError($errors));
        }

        $adminUserResult = $this->service->prepareForCreate(
            $name,
            $email,
            $role,
            $permissions,
        );

        if ($adminUserResult->isErr()) {
            return new Err($this->handleError($adminUserResult->unwrapErr()));
        }

        $passwordResult = HashedPassword::create($this->hasher->hash($plainPassword));

        if ($passwordResult->isErr()) {
            return new Err($this->handleError($passwordResult->unwrapErr()));
        }

        return new Ok($this->repository->register($adminUserResult->unwrap(), $passwordResult->unwrap()));
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

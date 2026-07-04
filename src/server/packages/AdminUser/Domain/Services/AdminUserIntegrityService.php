<?php

declare(strict_types=1);

namespace AdminUser\Domain\Services;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserName;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\CreatedAt;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\Role;
use DateTimeImmutable;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;

class AdminUserIntegrityService
{
    public function __construct(
        private readonly ClockInterface $clock,
        private readonly UuidGeneratorInterface $generator,
        private readonly AdminUserRepositoryInterface $repository,
    ) {
    }

    /**
     * @param list<string> $permissions
     *
     * @return Result<AdminUser, DomainError>
     */
    public function prepareForCreate(string $name, string $email, int $role, array $permissions): Result
    {
        $result = $this->build($this->generator->generate(), $name, $email, $this->clock->now(), $role, $permissions);

        if ($result->isErr()) {
            return new Err($result->unwrapErr());
        }

        $user = $result->unwrap();

        if (! is_null($this->repository->findByEmail($user->email))) {
            return new Err(new BusinessRuleViolationError(sprintf('すでに使われているメールアドレスです "%s"', $email)));
        }

        return new Ok($user);
    }

    /**
     * @param list<string> $permissions
     *
     * @return Result<AdminUser, DomainError>
     */
    public function prepareForCreateWithId(
        string $adminUserId,
        string $name,
        string $email,
        int $role,
        array $permissions,
    ): Result {
        $result = $this->build($adminUserId, $name, $email, $this->clock->now(), $role, $permissions);

        if ($result->isErr()) {
            return new Err($result->unwrapErr());
        }

        $user = $result->unwrap();

        if (! is_null($this->repository->findByEmailForUpdate($user->email))) {
            return new Err(new BusinessRuleViolationError(sprintf('すでに使われているメールアドレスです "%s"', $email)));
        }

        return new Ok($user);
    }

    /**
     * @param list<string> $permissions
     *
     * @return Result<AdminUser, DomainError>
     */
    private function build(
        string $adminUserId,
        string $name,
        string $email,
        DateTimeImmutable $createdAt,
        int $role,
        array $permissions,
    ): Result {
        return Result::collect6(
            AdminUserId::create($adminUserId),
            AdminUserName::create($name),
            Email::create($email),
            CreatedAt::create($createdAt),
            $this->toRole($role),
            Permissions::fromArray($permissions),
        )
            ->mapErr(static function (array $errors): DomainValidationError {
                $messages = [];
                foreach ($errors as $error) {
                    if ($error instanceof EntityRuleViolationError) {
                        $messages[$error->field] ??= [];
                        $messages[$error->field][] = $error->message;
                    }
                }

                return new DomainValidationError($messages);
            })
            ->map(static fn (array $values): AdminUser => new AdminUser(...$values));
    }

    /**
     * @return Result<Role, DomainError>
     */
    private function toRole(int $role): Result
    {
        $result = Role::tryFrom($role);

        if (is_null($result)) {
            return new Err(new EntityRuleViolationError(Role::class, "不正なロールです: {$role}"));
        }

        return new Ok($result);
    }
}

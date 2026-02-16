<?php

declare(strict_types=1);

namespace AdminUser\Domain\Services;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserFactoryInterface;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\PlainPassword;
use AdminUser\Domain\Models\Role;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use SensitiveParameter;
use Support\Contracts\UuidGeneratorInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainRuleViolationError;
use Support\Domain\Error\DomainValidationError;

class AdminUserIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly AdminUserFactoryInterface $factory,
        private readonly AdminUserRepositoryInterface $repository,
    ) {
    }

    /**
     * @param list<string> $permissions
     *
     * @return Result<AdminUser, DomainError>
     */
    public function prepareForCreate(string $email, #[SensitiveParameter] string $plainPassword, int $role, array $permissions): Result
    {
        $result = $this->build($this->generator->generate(), $email, $plainPassword, $role, $permissions);

        if ($result->isErr()) {
            return new Err($result->unwrapErr());
        }

        $user = $result->unwrap();

        if (! is_null($this->repository->findByEmail($user->email))) {
            return new Err(new DomainRuleViolationError(Email::class, sprintf('すでに使われているメールアドレスです "%s"', $email)));
        }

        return new Ok($user);
    }

    /**
     * @param list<string> $permissions
     *
     * @return Result<AdminUser, DomainError>
     */
    private function build(
        string $userId,
        string $email,
        #[SensitiveParameter]
        string $plainPassword,
        int $role,
        array $permissions,
    ): Result {
        return Result::collect5(
            AdminUserId::create($userId),
            Email::create($email),
            PlainPassword::create($plainPassword),
            $this->toRole($role),
            Permissions::fromArray($permissions),
        )
            ->mapErr(function (array $errors): DomainValidationError {
                $messages = [];
                foreach ($errors as $error) {
                    if ($error instanceof DomainRuleViolationError) {
                        $messages[$error->field] ??= [];
                        $messages[$error->field][] = $error->message;
                    }
                }

                return new DomainValidationError($messages);
            })
            ->map(fn (array $values): AdminUser => $this->factory->create(...$values));
    }

    /**
     * @return Result<Role, DomainError>
     */
    private function toRole(int $role): Result
    {
        $result = Role::tryFrom($role);

        if (is_null($result)) {
            return new Err(new DomainRuleViolationError(Role::class, "不正なロールです: {$role}"));
        }

        return new Ok($result);
    }
}

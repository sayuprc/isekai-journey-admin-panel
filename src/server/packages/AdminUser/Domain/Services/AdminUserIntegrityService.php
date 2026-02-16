<?php

declare(strict_types=1);

namespace AdminUser\Domain\Services;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserFactoryInterface;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\PlainPassword;
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
     * @return Result<AdminUser, DomainError>
     */
    public function prepareForCreate(string $email, #[SensitiveParameter] string $plainPassword): Result
    {
        $result = $this->build($this->generator->generate(), $email, $plainPassword);

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
     * @return Result<AdminUser, DomainError>
     */
    private function build(string $userId, string $email, #[SensitiveParameter] string $plainPassword): Result
    {
        return Result::collect3(
            AdminUserId::create($userId),
            Email::create($email),
            PlainPassword::create($plainPassword),
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
}

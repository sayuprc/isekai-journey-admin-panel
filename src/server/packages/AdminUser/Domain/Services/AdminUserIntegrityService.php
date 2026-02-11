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

/**
 * TODO エラーハンドリングを強化する
 */
class AdminUserIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly AdminUserFactoryInterface $factory,
        private readonly AdminUserRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<AdminUser, string>
     */
    public function prepareForCreate(string $email, #[SensitiveParameter] string $plainPassword): Result
    {
        $result = $this->build($this->generator->generate(), $email, $plainPassword);

        if ($result->isErr()) {
            return new Err('');
        }

        $user = $result->unwrap();

        if (! is_null($this->repository->findByEmail($user->email))) {
            return new Err(sprintf('すでに使われているメールアドレスです "%s"', $email));
        }

        return new Ok($user);
    }

    /**
     * @return Result<AdminUser, string>
     */
    private function build(string $userId, string $email, #[SensitiveParameter] string $plainPassword): Result
    {
        return Result::collect3(
            AdminUserId::create($userId),
            Email::create($email),
            PlainPassword::create($plainPassword),
        )
            ->mapErr(fn (): string => '')
            ->map(fn (array $values): AdminUser => $this->factory->create(...$values));
    }
}

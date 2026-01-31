<?php

declare(strict_types=1);

namespace User\Domain\Services;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use SensitiveParameter;
use Support\Contracts\UuidGeneratorInterface;
use User\Domain\Models\Email;
use User\Domain\Models\PlainPassword;
use User\Domain\Models\User;
use User\Domain\Models\UserFactoryInterface;
use User\Domain\Models\UserId;
use User\Domain\Models\UserRepositoryInterface;

/**
 * TODO エラーハンドリングを強化する
 */
class UserIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly UserFactoryInterface $factory,
        private readonly UserRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<User, string>
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
     * @return Result<User, string>
     */
    private function build(string $userId, string $email, #[SensitiveParameter] string $plainPassword): Result
    {
        return Result::collect3(
            UserId::create($userId),
            Email::create($email),
            PlainPassword::create($plainPassword),
        )
            ->mapErr(fn (): string => '')
            ->map(fn (array $values): User => $this->factory->create(...$values));
    }
}

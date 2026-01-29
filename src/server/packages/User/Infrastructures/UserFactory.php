<?php

declare(strict_types=1);

namespace User\Infrastructures;

use ResultType\Result;
use Support\Contracts\UuidGeneratorInterface;
use User\Domain\Models\Email;
use User\Domain\Models\HashedPassword;
use User\Domain\Models\User;
use User\Domain\Models\UserFactoryInterface;
use User\Domain\Models\UserId;
use User\Domain\Services\HasherInterface;

readonly class UserFactory implements UserFactoryInterface
{
    public function __construct(
        private UuidGeneratorInterface $generator,
        private HasherInterface $hasher,
    ) {
    }

    public function create(string $email, string $plainPassword): Result
    {
        return Result::collect3(
            UserId::create($this->generator->generate()),
            Email::create($email),
            HashedPassword::create($this->hasher->hash($plainPassword)),
        )->map(fn (array $values): User => new User(...$values))
            ->mapErr(fn (array $values): array => array_filter($values, fn ($item) => ! is_null($item)));
    }
}

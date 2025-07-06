<?php

declare(strict_types=1);

namespace User\Infrastructures;

use Support\Contracts\UuidGeneratorInterface;
use User\Domain\Models\Email;
use User\Domain\Models\HashedPassword;
use User\Domain\Models\User;
use User\Domain\Models\UserFactoryInterface;
use User\Domain\Models\UserId;
use User\Domain\Services\HasherInterface;

class UserFactory implements UserFactoryInterface
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly HasherInterface $hasher,
    ) {
    }

    public function create(string $email, string $plainPassword): User
    {
        return new User(
            new UserId($this->generator->generate()),
            new Email($email),
            new HashedPassword($this->hasher->hash($plainPassword))
        );
    }
}

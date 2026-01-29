<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use User\Domain\Models\Email;
use User\Domain\Models\User;
use User\Domain\Models\UserId;
use User\Domain\Models\UserRepositoryInterface;
use User\Domain\Services\HasherInterface;

readonly class AuthUserProvider implements UserProvider
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private HasherInterface $hasher,
    ) {
    }

    /**
     * @param string $identifier
     */
    public function retrieveById($identifier)
    {
        return UserId::create($identifier)
            ->match(
                fn (UserId $userId): ?AuthUser => $this->toAuthUser($this->repository->find($userId)),
                fn () => null,
            );
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
    }

    /**
     * @param array{email: string, password: string} $credentials
     */
    public function retrieveByCredentials(array $credentials)
    {
        return Email::create($credentials['email'])
            ->match(
                fn (Email $email): ?AuthUser => $this->toAuthUser($this->repository->findByEmail($email)),
                fn () => null,
            );
    }

    /**
     * @param array{password: string} $credentials
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->hasher->check($credentials['password'], $user->getAuthPassword());
    }

    /**
     * @param array<mixed> $credentials
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
    }

    private function toAuthUser(?User $user): ?AuthUser
    {
        if (is_null($user)) {
            return null;
        }

        return new AuthUser($user->userId, $user->email, $user->hashedPassword);
    }
}

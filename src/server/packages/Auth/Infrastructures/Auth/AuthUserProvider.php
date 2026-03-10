<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Auth;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Services\HasherInterface;
use Auth\Domain\Models\AuthAdminUserRepositoryInterface;
use Auth\Domain\Models\AuthenticatableAdminUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Override;

readonly class AuthUserProvider implements UserProvider
{
    public function __construct(
        private AuthAdminUserRepositoryInterface $repository,
        private HasherInterface $hasher,
    ) {
    }

    /**
     * @param string $identifier
     */
    #[Override]
    public function retrieveById($identifier)
    {
        return AdminUserId::create($identifier)
            ->match(
                fn (AdminUserId $adminUserId): ?AuthUser => $this->toAuthUser($this->repository->find($adminUserId)),
                fn () => null,
            );
    }

    #[Override]
    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    #[Override]
    public function updateRememberToken(Authenticatable $user, $token)
    {
    }

    /**
     * @param array{email: string, password: string} $credentials
     */
    #[Override]
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
    #[Override]
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->hasher->check($credentials['password'], $user->getAuthPassword());
    }

    /**
     * @param array<mixed> $credentials
     */
    #[Override]
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
    }

    private function toAuthUser(?AuthenticatableAdminUser $user): ?AuthUser
    {
        if (is_null($user)) {
            return null;
        }

        return new AuthUser($user->adminUserId, $user->hashedPassword);
    }
}

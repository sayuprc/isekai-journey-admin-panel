<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Auth;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\Email;
use Auth\Domain\Models\AuthAdminUserRepositoryInterface;
use Auth\Domain\Models\AuthenticatableAdminUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

readonly class AuthUserProvider implements UserProvider
{
    public function __construct(
        private AuthAdminUserRepositoryInterface $repository,
    ) {
    }

    /**
     * @param string $identifier
     */
    public function retrieveById($identifier)
    {
        return AdminUserId::create($identifier)
            ->match(
                fn (AdminUserId $adminUserId): ?AuthUser => $this->toAuthUser($this->repository->find($adminUserId)),
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
     * @param array{password?: string} $credentials
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return false;
    }

    /**
     * @param array<mixed> $credentials
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
    }

    private function toAuthUser(?AuthenticatableAdminUser $user): ?AuthUser
    {
        if (is_null($user)) {
            return null;
        }

        return new AuthUser($user->adminUserId);
    }
}

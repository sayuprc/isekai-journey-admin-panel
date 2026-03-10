<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Auth;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\Email;
use App\Models\AdminUser\AdminUser as ModelsAdminUser;
use Auth\Domain\Models\AuthAdminUserRepositoryInterface;
use Auth\Domain\Models\AuthenticatableAdminUser;
use Support\Contracts\Uuid\UuidConverterInterface;
use Override;

readonly class AuthAdminUserRepository implements AuthAdminUserRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function find(AdminUserId $adminUserId): ?AuthenticatableAdminUser
    {
        $found = ModelsAdminUser::query()
            ->without('permissions')
            ->where('admin_user_id', $this->converter->toBin($adminUserId->value))
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    #[Override]
    public function findByEmail(Email $email): ?AuthenticatableAdminUser
    {
        $found = ModelsAdminUser::query()
            ->without('permissions')
            ->where('email', $email->value)
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    private function hydrate(ModelsAdminUser $model): AuthenticatableAdminUser
    {
        return AuthenticatableAdminUser::reconstruct(
            $this->converter->toUuid($model->admin_user_id),
            $model->password,
        );
    }
}

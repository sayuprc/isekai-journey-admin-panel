<?php

declare(strict_types=1);

namespace AdminUser\Infrastructures;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\HashedPassword;
use App\Models\AdminUser\AdminUser as ModelsAdminUser;
use App\Models\AdminUser\AdminUserPermission;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
readonly class AdminUserRepository implements AdminUserRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function all(): array
    {
        return ModelsAdminUser::query()
            ->orderBy('created_at')
            ->get()
            ->map($this->hydrate(...))
            ->all();
    }

    #[Override]
    public function find(AdminUserId $adminUserId): ?AdminUser
    {
        $found = ModelsAdminUser::query()
            ->where('admin_user_id', $this->converter->toBin($adminUserId->value))
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    #[Override]
    public function findByEmail(Email $email): ?AdminUser
    {
        $found = ModelsAdminUser::query()
            ->where('email', $email->value)
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    #[Override]
    public function register(AdminUser $adminUser, HashedPassword $hashedPassword): AdminUser
    {
        $data = $adminUser->toArray();

        $id = $this->converter->toBin($data['admin_user_id']);

        ModelsAdminUser::query()->insert(
            [
                'admin_user_id' => $id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $hashedPassword->value,
                'role' => $data['role'],
                'created_at' => $data['created_at'],
                'updated_at' => now(),
            ],
        );

        if ($data['permissions'] !== []) {
            AdminUserPermission::query()->insert(
                array_map(fn (string $permission): array => [
                    'admin_user_id' => $id,
                    'permission' => $permission,
                ], $data['permissions']),
            );
        }

        return $adminUser;
    }

    private function hydrate(ModelsAdminUser $model): AdminUser
    {
        /** @var list<string> */
        $permissions = $model->permissions->map(fn (AdminUserPermission $row): string => $row->permission)->all();

        return AdminUser::reconstruct(
            $this->converter->toUuid($model->admin_user_id),
            $model->name,
            $model->email,
            $model->created_at->toDateTimeImmutable(),
            $model->role,
            $permissions,
        );
    }
}

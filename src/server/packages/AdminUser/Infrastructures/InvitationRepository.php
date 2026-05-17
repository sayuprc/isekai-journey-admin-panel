<?php

declare(strict_types=1);

namespace AdminUser\Infrastructures;

use AdminUser\Domain\Models\Invitation\HashedToken;
use AdminUser\Domain\Models\Invitation\Invitation;
use AdminUser\Domain\Models\Invitation\InvitationRepositoryInterface;
use App\Models\AdminUser\AdminUserInvitation as ModelsAdminUserInvitation;
use App\Models\AdminUser\AdminUserInvitationPermission;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class InvitationRepository implements InvitationRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function save(Invitation $invitation): Invitation
    {
        $data = $invitation->toArray();

        $id = $this->converter->toBin($data['invitation_id']);

        $exists = ModelsAdminUserInvitation::query()
            ->where('invitation_id', $id)
            ->exists();

        if ($exists) {
            ModelsAdminUserInvitation::query()
                ->where('invitation_id', $id)
                ->update([
                    'token_hash' => $data['hashed_token'],
                    'role' => $data['role'],
                    'expires_at' => $data['expires_at'],
                    'consumed_at' => $data['consumed_at'],
                    'updated_at' => now(),
                ]);

            AdminUserInvitationPermission::query()
                ->where('invitation_id', $id)
                ->delete();
        } else {
            ModelsAdminUserInvitation::query()->insert([
                'invitation_id' => $id,
                'token_hash' => $data['hashed_token'],
                'role' => $data['role'],
                'expires_at' => $data['expires_at'],
                'consumed_at' => $data['consumed_at'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($data['permissions'] !== []) {
            AdminUserInvitationPermission::query()->insert(
                array_map(fn (string $permission): array => [
                    'invitation_id' => $id,
                    'permission' => $permission,
                ], $data['permissions']),
            );
        }

        return $invitation;
    }

    #[Override]
    public function findByHashedToken(HashedToken $hashedToken): ?Invitation
    {
        $found = ModelsAdminUserInvitation::query()
            ->where('token_hash', $hashedToken->value)
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    private function hydrate(ModelsAdminUserInvitation $model): Invitation
    {
        /** @var list<string> */
        $permissions = $model->permissions->map(fn (AdminUserInvitationPermission $row): string => $row->permission)->all();

        return Invitation::reconstruct(
            $this->converter->toUuid($model->invitation_id),
            $model->token_hash,
            $model->role,
            $permissions,
            $model->expires_at->toDateTimeImmutable(),
            $model->consumed_at?->toDateTimeImmutable(),
        );
    }
}

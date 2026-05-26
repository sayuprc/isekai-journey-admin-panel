<?php

declare(strict_types=1);

namespace Auth\Infrastructures;

use App\Models\AdminUser\AdminUserPasskey as Model;
use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Illuminate\Support\Carbon;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class AdminUserPasskeyRepository implements AdminUserPasskeyRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    /**
     * @return list<AdminUserPasskey>
     */
    #[Override]
    public function findByAdminUserId(string $adminUserId): array
    {
        return array_values(Model::query()
            ->where('admin_user_id', $this->converter->toBin($adminUserId))
            ->orderBy('created_at')
            ->get()
            ->map($this->hydrate(...))
            ->all());
    }

    #[Override]
    public function findByCredentialId(string $credentialId): ?AdminUserPasskey
    {
        $found = Model::query()
            ->where('credential_id', $credentialId)
            ->first();

        return is_null($found) ? null : $this->hydrate($found);
    }

    #[Override]
    public function findByUserHandle(string $userHandle): ?AdminUserPasskey
    {
        $found = Model::query()
            ->where('user_handle', $userHandle)
            ->orderBy('created_at')
            ->first();

        return is_null($found) ? null : $this->hydrate($found);
    }

    #[Override]
    public function save(AdminUserPasskey $passkey): AdminUserPasskey
    {
        Model::query()->insert($this->toPersistence($passkey));

        return $passkey;
    }

    #[Override]
    public function update(AdminUserPasskey $passkey): AdminUserPasskey
    {
        Model::query()
            ->where('admin_user_passkey_id', $this->converter->toBin($passkey->adminUserPasskeyId))
            ->update([
                'sign_count' => $passkey->signCount,
                'last_used_at' => $passkey->lastUsedAt?->format('Y-m-d H:i:s'),
                'updated_at' => now(),
            ]);

        return $passkey;
    }

    private function hydrate(Model $model): AdminUserPasskey
    {
        return new AdminUserPasskey(
            $this->converter->toUuid($model->admin_user_passkey_id),
            $this->converter->toUuid($model->admin_user_id),
            $model->user_handle,
            $model->name,
            $model->credential_id,
            $model->public_key,
            $model->aaguid,
            $this->transports($model->transports),
            $model->backup_eligible,
            $model->backup_state,
            $model->sign_count,
            $model->created_at->toDateTimeImmutable(),
            $model->last_used_at?->toDateTimeImmutable(),
        );
    }

    /**
     * @return array{
     *   admin_user_passkey_id: string,
     *   admin_user_id: string,
     *   user_handle: string,
     *   name: string,
     *   credential_id: string,
     *   public_key: string,
     *   aaguid: string,
     *   transports: string,
     *   backup_eligible: bool|null,
     *   backup_state: bool|null,
     *   sign_count: int,
     *   last_used_at: string|null,
     *   created_at: string,
     *   updated_at: Carbon
     * }
     */
    private function toPersistence(AdminUserPasskey $passkey): array
    {
        return [
            'admin_user_passkey_id' => $this->converter->toBin($passkey->adminUserPasskeyId),
            'admin_user_id' => $this->converter->toBin($passkey->adminUserId),
            'user_handle' => $passkey->userHandle,
            'name' => $passkey->name,
            'credential_id' => $passkey->credentialId,
            'public_key' => $passkey->publicKey,
            'aaguid' => $passkey->aaguid,
            'transports' => json_encode($passkey->transports, JSON_THROW_ON_ERROR),
            'backup_eligible' => $passkey->backupEligible,
            'backup_state' => $passkey->backupState,
            'sign_count' => $passkey->signCount,
            'last_used_at' => $passkey->lastUsedAt?->format('Y-m-d H:i:s'),
            'created_at' => $passkey->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => now(),
        ];
    }

    /**
     * @return list<string>
     */
    private function transports(mixed $transports): array
    {
        if (! is_array($transports)) {
            return [];
        }

        return array_filter($transports, fn (mixed $transport): bool => is_string($transport))
            |> array_values(...);
    }
}

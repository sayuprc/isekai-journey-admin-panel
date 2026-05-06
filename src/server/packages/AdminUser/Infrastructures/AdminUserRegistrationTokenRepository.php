<?php

declare(strict_types=1);

namespace AdminUser\Infrastructures;

use AdminUser\Domain\Models\AdminUserRegistrationToken;
use AdminUser\Domain\Models\AdminUserRegistrationTokenId;
use AdminUser\Domain\Models\AdminUserRegistrationTokenRepositoryInterface;
use AdminUser\Domain\Models\Email;
use App\Models\AdminUser\AdminUserRegistrationToken as Model;
use DateTimeImmutable;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class AdminUserRegistrationTokenRepository implements AdminUserRegistrationTokenRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function find(AdminUserRegistrationTokenId $adminUserRegistrationTokenId): ?AdminUserRegistrationToken
    {
        $found = Model::query()
            ->where('admin_user_registration_token_id', $this->converter->toBin($adminUserRegistrationTokenId->value))
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    /**
     * @return list<AdminUserRegistrationToken>
     */
    #[Override]
    public function findByEmail(Email $email): array
    {
        return array_values(Model::query()
            ->where('email', $email->value)
            ->orderByDesc('created_at')
            ->get()
            ->map($this->hydrate(...))
            ->all());
    }

    #[Override]
    public function save(AdminUserRegistrationToken $token): AdminUserRegistrationToken
    {
        $data = $token->toArray();

        Model::query()->insert([
            'admin_user_registration_token_id' => $this->converter->toBin($data['admin_user_registration_token_id']),
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'token_hash' => $data['token_hash'],
            'expired_at' => $data['expired_at'],
            'used_at' => $data['used_at'],
            'created_at' => $data['created_at'],
            'updated_at' => now(),
        ]);

        return $token;
    }

    #[Override]
    public function markUsed(AdminUserRegistrationTokenId $adminUserRegistrationTokenId, DateTimeImmutable $usedAt): void
    {
        Model::query()
            ->where('admin_user_registration_token_id', $this->converter->toBin($adminUserRegistrationTokenId->value))
            ->update([
                'used_at' => $usedAt->format('Y-m-d H:i:s'),
                'updated_at' => now(),
            ]);
    }

    private function hydrate(Model $model): AdminUserRegistrationToken
    {
        return AdminUserRegistrationToken::reconstruct(
            $this->converter->toUuid($model->admin_user_registration_token_id),
            $model->name,
            $model->email,
            $model->role,
            $model->token_hash,
            $model->expired_at->toDateTimeImmutable(),
            $model->created_at->toDateTimeImmutable(),
            $model->used_at?->toDateTimeImmutable(),
        );
    }
}

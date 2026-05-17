<?php

declare(strict_types=1);

namespace AdminUser\Infrastructures\RegistrationToken;

use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use App\Models\AdminUser\RegistrationToken as ModelsRegistrationToken;
use App\Models\AdminUser\RegistrationTokenPermission;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class RegistrationTokenRepository implements RegistrationTokenRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function save(RegistrationToken $token): RegistrationToken
    {
        $data = $token->toArray();

        $id = $this->converter->toBin($data['admin_user_registration_token_id']);

        ModelsRegistrationToken::query()->insert(
            [
                'admin_user_registration_token_id' => $id,
                'token' => $data['token'],
                'email' => $data['email'],
                'role' => $data['role'],
                'expired_at' => $data['expired_at'],
                'status' => $data['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        if ($data['permissions'] !== []) {
            RegistrationTokenPermission::query()->insert(
                array_map(fn (string $permission): array => [
                    'admin_user_registration_token_id' => $id,
                    'permission' => $permission,
                ], $data['permissions']),
            );
        }

        return $token;
    }
}

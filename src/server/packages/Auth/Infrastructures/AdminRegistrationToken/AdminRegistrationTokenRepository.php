<?php

declare(strict_types=1);

namespace Auth\Infrastructures\AdminRegistrationToken;

use AdminUser\Domain\Models\Email;
use App\Models\Auth\AdminRegistrationToken as AuthAdminRegistrationToken;
use Auth\Domain\Models\AdminRegistrationToken\AdminRegistrationToken;
use Auth\Domain\Models\AdminRegistrationToken\AdminRegistrationTokenRepositoryInterface;
use Auth\Domain\Models\AdminRegistrationToken\ConsumptionStatus;
use Override;
use Support\Contracts\ClockInterface;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class AdminRegistrationTokenRepository implements AdminRegistrationTokenRepositoryInterface
{
    public function __construct(
        private UuidConverterInterface $converter,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @return list<AdminRegistrationToken>
     */
    #[Override]
    public function findAvailableByEmail(Email $email): array
    {
        /** @var list<AuthAdminRegistrationToken> $tokens */
        $tokens = AuthAdminRegistrationToken::query()
            ->where('email', $email->value)
            ->where('status', ConsumptionStatus::Unused->value)
            ->where('expired_at', '>=', $this->clock->now())
            ->orderByDesc('created_at')
            ->get()
            ->all();

        return array_map($this->hydrate(...), $tokens);
    }

    #[Override]
    public function save(AdminRegistrationToken $adminRegistrationToken): AdminRegistrationToken
    {
        $data = $adminRegistrationToken->toArray();
        $id = $this->converter->toBin($data['admin_registration_token_id']);

        AuthAdminRegistrationToken::query()->upsert(
            [
                ...$data,
                'admin_registration_token_id' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            ['admin_registration_token_id'],
            [
                'status',
                'updated_at',
            ],
        );

        return $adminRegistrationToken;
    }

    private function hydrate(AuthAdminRegistrationToken $model): AdminRegistrationToken
    {
        return AdminRegistrationToken::reconstruct(
            $this->converter->toUuid($model->admin_registration_token_id),
            $model->email,
            $model->role,
            $model->token,
            $model->expired_at->toDateTimeImmutable(),
            $model->status,
        );
    }
}

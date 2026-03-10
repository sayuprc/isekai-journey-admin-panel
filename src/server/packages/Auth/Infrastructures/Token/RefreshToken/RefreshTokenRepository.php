<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Token\RefreshToken;

use Override;
use App\Models\Auth\RefreshToken as AuthRefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Support\Contracts\ClockInterface;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(
        private UuidConverterInterface $converter,
        private ClockInterface $clock,
    ) {
    }

    #[Override]
    public function findActive(RefreshTokenId $refreshTokenId): ?RefreshToken
    {
        $found = AuthRefreshToken::query()
            ->where('refresh_token_id', $this->converter->toBin($refreshTokenId->value))
            ->first();

        if (is_null($found)) {
            return null;
        }

        $hydrated = $this->hydrate($found);

        return ! $hydrated->isAvailable($this->clock->now())
            ? null
            : $hydrated;
    }

    #[Override]
    public function save(RefreshToken $refreshToken): RefreshToken
    {
        $data = $refreshToken->toArray();

        $id = $this->converter->toBin($data['refresh_token_id']);
        $adminUserId = $this->converter->toBin($data['admin_user_id']);

        AuthRefreshToken::query()->upsert(
            [
                ...$refreshToken->toArray(),
                'refresh_token_id' => $id,
                'admin_user_id' => $adminUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            ['refresh_token_id'],
            [
                'status',
                'updated_at',
            ],
        );

        return $refreshToken;
    }

    private function hydrate(AuthRefreshToken $model): RefreshToken
    {
        return RefreshToken::reconstruct(
            $this->converter->toUuid($model->refresh_token_id),
            $this->converter->toUuid($model->admin_user_id),
            $model->token,
            $model->expired_at->toDateTimeImmutable(),
            $model->status,
        );
    }
}

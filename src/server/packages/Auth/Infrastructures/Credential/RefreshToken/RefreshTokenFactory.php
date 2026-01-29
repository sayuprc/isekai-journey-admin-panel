<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Credential\RefreshToken;

use Auth\Domain\Models\Credential\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Credential\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Credential\RefreshToken\RefreshToken;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenFactoryInterface;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Credential\RefreshToken\TokenValue;
use Auth\Domain\Services\Credential\RefreshToken\RandomTokenGeneratorInterface;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Support\Contracts\UuidGeneratorInterface;
use User\Domain\Models\UserId;

readonly class RefreshTokenFactory implements RefreshTokenFactoryInterface
{
    private const int TTL_DAY = 7;

    public function __construct(
        private ClockInterface $clock,
        private UuidGeneratorInterface $uuid,
        private RandomTokenGeneratorInterface $randomToken,
    ) {
    }

    public function create(string $userId): Result
    {
        return Result::collect4(
            RefreshTokenId::create($this->uuid->generate()),
            UserId::create($userId),
            TokenValue::create($this->randomToken->generate()),
            ExpiredAt::create($this->clock->now()->modify('+' . self::TTL_DAY . ' days')),
        )->map(fn (array $values): RefreshToken => new RefreshToken(...[...$values, ConsumptionStatus::Unused]))
            ->mapErr(fn (array $errors): array => array_filter($errors, fn ($item) => ! is_null($item)));
    }
}

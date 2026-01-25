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

    public function create(string $userId): RefreshToken
    {
        return new RefreshToken(
            new RefreshTokenId($this->uuid->generate()),
            new UserId($userId),
            new TokenValue($this->randomToken->generate()),
            new ExpiredAt($this->clock->now()->modify('+' . self::TTL_DAY . ' days')),
            ConsumptionStatus::Unused,
        );
    }
}

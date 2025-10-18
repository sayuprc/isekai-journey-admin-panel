<?php

declare(strict_types=1);

namespace User\Infrastructures\Credential\RefreshToken;

use Support\Contracts\ClockInterface;
use Support\Contracts\UuidGeneratorInterface;
use User\Domain\Models\Credential\RefreshToken\ExpiredAt;
use User\Domain\Models\Credential\RefreshToken\IsUsed;
use User\Domain\Models\Credential\RefreshToken\RefreshToken;
use User\Domain\Models\Credential\RefreshToken\RefreshTokenFactoryInterface;
use User\Domain\Models\Credential\RefreshToken\RefreshTokenId;
use User\Domain\Models\Credential\RefreshToken\TokenValue;
use User\Domain\Models\UserId;
use User\Domain\Services\Credential\RefreshToken\RandomTokenGeneratorInterface;

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
            new IsUsed(false),
        );
    }
}

<?php

declare(strict_types=1);

namespace User\Infrastructures\Credential;

use Support\Contracts\ClockInterface;
use User\Domain\Models\Credential\ExpiredAt;
use User\Domain\Models\Credential\IsEnabled;
use User\Domain\Models\Credential\RefreshToken;
use User\Domain\Models\Credential\RefreshTokenFactoryInterface;
use User\Domain\Models\Credential\TokenValue;
use User\Domain\Services\RandomTokenGeneratorInterface;

class RefreshTokenFactory implements RefreshTokenFactoryInterface
{
    private const int TTL_DAY = 7;

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly RandomTokenGeneratorInterface $generator,
    ) {
    }

    public function create(): RefreshToken
    {
        return new RefreshToken(
            new TokenValue($this->generator->generate()),
            new ExpiredAt($this->clock->now()->modify('+' . self::TTL_DAY . ' days')),
            new IsEnabled(true),
        );
    }
}

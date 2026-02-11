<?php

declare(strict_types=1);

namespace Auth\Domain\Services\Credential\RefreshToken;

use AdminUser\Domain\Models\AdminUserId;
use Auth\Domain\Models\Credential\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Credential\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Credential\RefreshToken\RefreshToken;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenFactoryInterface;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Credential\RefreshToken\TokenValue;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Support\Contracts\UuidGeneratorInterface;

/**
 * TODO エラーハンドリングを強化する
 */
class RefreshTokenIssueService
{
    private const int TTL_DAY = 7;

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly UuidGeneratorInterface $uuidGenerator,
        private readonly RandomTokenGeneratorInterface $randomTokenGenerator,
        private readonly RefreshTokenFactoryInterface $factory,
    ) {
    }

    /**
     * @return Result<RefreshToken, string>
     */
    public function issue(string $userId): Result
    {
        $result = Result::collect4(
            RefreshTokenId::create($this->uuidGenerator->generate()),
            AdminUserId::create($userId),
            TokenValue::create($this->randomTokenGenerator->generate()),
            ExpiredAt::create($this->clock->now()->modify('+' . self::TTL_DAY . ' days')),
        )->map(fn (array $values): RefreshToken => $this->factory->create(...[...$values, ConsumptionStatus::Unused]));

        if ($result->isErr()) {
            return new Err('');
        }

        return new Ok($result->unwrap());
    }
}

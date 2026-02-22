<?php

declare(strict_types=1);

namespace Auth\Domain\Services\Token\RefreshToken;

use AdminUser\Domain\Models\AdminUserId;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenFactoryInterface;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Token\RefreshToken\TokenValue;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Support\Contracts\UuidGeneratorInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainRuleViolationError;
use Support\Domain\Error\DomainValidationError;

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
     * @return Result<RefreshToken, DomainError>
     */
    public function issue(string $adminUserId): Result
    {
        $result = Result::collect4(
            RefreshTokenId::create($this->uuidGenerator->generate()),
            AdminUserId::create($adminUserId),
            TokenValue::create($this->randomTokenGenerator->generate()),
            ExpiredAt::create($this->clock->now()->modify('+' . self::TTL_DAY . ' days')),
        )->map(fn (array $values): RefreshToken => $this->factory->create(...[...$values, ConsumptionStatus::Unused]));

        if ($result->isErr()) {
            $messages = [];
            foreach ($result->unwrapErr() as $error) {
                if ($error instanceof DomainRuleViolationError) {
                    $messages[$error->field] ??= [];
                    $messages[$error->field][] = $error->message;
                }
            }

            return new Err(new DomainValidationError($messages));
        }

        return new Ok($result->unwrap());
    }
}

<?php

declare(strict_types=1);

namespace AdminUser\Domain\Services\RegistrationToken;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use SensitiveParameter;
use Support\Contracts\ClockInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;

class RegistrationTokenConsumeService
{
    public function __construct(
        private readonly ClockInterface $clock,
        private readonly TokenHasherInterface $tokenHasher,
        private readonly RegistrationTokenRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<RegistrationToken, DomainError>
     */
    public function verify(#[SensitiveParameter] string $plainToken, Email $email): Result
    {
        $token = $this->repository->findByEmailForUpdate($email);

        if (is_null($token)) {
            return new Err(new BusinessRuleViolationError('token_not_found'));
        }

        if (! $this->tokenHasher->verify($plainToken, $token->token->value)) {
            return new Err(new BusinessRuleViolationError('token_not_found'));
        }

        if (! $token->isAvailable($this->clock->now())) {
            return new Err(new BusinessRuleViolationError('token_not_found'));
        }

        return new Ok($token);
    }
}

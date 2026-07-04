<?php

declare(strict_types=1);

namespace AdminUser\Domain\Services\RegistrationToken;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\RegistrationToken\ConsumptionStatus;
use AdminUser\Domain\Models\RegistrationToken\ExpiredAt;
use AdminUser\Domain\Models\RegistrationToken\HashedTokenValue;
use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenId;
use AdminUser\Domain\Models\Role;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;

class RegistrationTokenIssueService
{
    private const int TTL_DAY = 7;

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly UuidGeneratorInterface $uuidGenerator,
        private readonly RandomTokenGeneratorInterface $randomTokenGenerator,
        private readonly TokenHasherInterface $tokenHasher,
    ) {
    }

    /**
     * @param list<string> $permissions
     *
     * @return Result<array{token: RegistrationToken, plainToken: string}, DomainError>
     */
    public function issue(Email $email, int $role, array $permissions): Result
    {
        $plainToken = $this->randomTokenGenerator->generate();
        $hashedToken = $this->tokenHasher->hash($plainToken);

        $result = Result::collect5(
            RegistrationTokenId::create($this->uuidGenerator->generate()),
            HashedTokenValue::create($hashedToken),
            $this->toRole($role),
            Permissions::fromArray($permissions),
            ExpiredAt::create($this->clock->now()->modify('+' . self::TTL_DAY . ' days')),
        )->map(static fn (array $values): RegistrationToken => new RegistrationToken(
            $values[0],
            $values[1],
            $email,
            $values[2],
            $values[3],
            $values[4],
            ConsumptionStatus::Unused,
        ));

        if ($result->isErr()) {
            $messages = [];
            foreach ($result->unwrapErr() as $error) {
                if ($error instanceof EntityRuleViolationError) {
                    $messages[$error->field] ??= [];
                    $messages[$error->field][] = $error->message;
                }
            }

            return new Err(new DomainValidationError($messages));
        }

        return new Ok([
            'token' => $result->unwrap(),
            'plainToken' => $plainToken,
        ]);
    }

    /**
     * @return Result<Role, EntityRuleViolationError>
     */
    private function toRole(int $role): Result
    {
        $result = Role::tryFrom($role);

        if (is_null($result)) {
            return new Err(new EntityRuleViolationError(Role::class, "不正なロールです: {$role}"));
        }

        return new Ok($result);
    }
}

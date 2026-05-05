<?php

declare(strict_types=1);

namespace AdminUser\Domain\Services;

use AdminUser\Domain\Models\AdminUserName;
use AdminUser\Domain\Models\AdminUserRegistrationToken;
use AdminUser\Domain\Models\AdminUserRegistrationTokenId;
use AdminUser\Domain\Models\CreatedAt;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\RegistrationTokenExpiredAt;
use AdminUser\Domain\Models\RegistrationTokenHashedValue;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Services\Token\RefreshToken\RandomTokenGeneratorInterface;
use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
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
     * @return Result<array{token: AdminUserRegistrationToken, plainToken: string}, DomainError>
     */
    public function issue(
        string $name,
        string $email,
        int $role,
        array $permissions,
        int $expiresInMinutes,
    ): Result {
        if ($expiresInMinutes <= 0) {
            return new Err(new DomainValidationError([
                'expiresInMinutes' => ['正の整数ではありません: ' . $expiresInMinutes],
            ]));
        }

        $plainToken = $this->randomTokenGenerator->generate();
        $hashedToken = $this->tokenHasher->hash($plainToken);
        $now = $this->clock->now();

        $result = Result::collect8(
            AdminUserRegistrationTokenId::create($this->uuidGenerator->generate()),
            AdminUserName::create($name),
            Email::create($email),
            $this->toRole($role),
            Permissions::fromArray($permissions),
            RegistrationTokenHashedValue::create($hashedToken),
            RegistrationTokenExpiredAt::create($now->modify('+' . $expiresInMinutes . ' minutes')),
            CreatedAt::create($now),
        )->map(fn (array $values): AdminUserRegistrationToken => new AdminUserRegistrationToken(...[...$values, null]));

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
     * @return Result<Role, DomainError>
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

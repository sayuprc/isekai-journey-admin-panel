<?php

declare(strict_types=1);

namespace Auth\Domain\Services\RecoveryCode;

use AdminUser\Domain\Models\AdminUserId;
use Auth\Domain\Models\RecoveryCode\ConsumptionStatus;
use Auth\Domain\Models\RecoveryCode\HashedCodeValue;
use Auth\Domain\Models\RecoveryCode\RecoveryCode;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeId;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;

class RecoveryCodeIssueService
{
    private const int CODE_COUNT = 10;

    public function __construct(
        private readonly UuidGeneratorInterface $uuidGenerator,
        private readonly RandomRecoveryCodeGeneratorInterface $randomRecoveryCodeGenerator,
        private readonly RecoveryCodeHasherInterface $recoveryCodeHasher,
    ) {
    }

    /**
     * @return Result<array{codes: list<RecoveryCode>, plainCodes: list<string>}, DomainError>
     */
    public function issue(AdminUserId $adminUserId): Result
    {
        $codes = [];
        $plainCodes = [];
        $messages = [];

        for ($i = 0; $i < self::CODE_COUNT; $i++) {
            $plainCode = $this->randomRecoveryCodeGenerator->generate();
            $hashedCode = $this->recoveryCodeHasher->hash($plainCode);

            $result = Result::collect(
                RecoveryCodeId::create($this->uuidGenerator->generate()),
                HashedCodeValue::create($hashedCode),
            )->map(fn (array $values): RecoveryCode => new RecoveryCode(
                $values[0],
                $adminUserId,
                $values[1],
                ConsumptionStatus::Unused,
                null,
            ));

            if ($result->isErr()) {
                foreach ($result->unwrapErr() as $error) {
                    if ($error instanceof EntityRuleViolationError) {
                        $messages[$error->field] ??= [];
                        $messages[$error->field][] = $error->message;
                    }
                }

                continue;
            }

            $codes[] = $result->unwrap();
            $plainCodes[] = $plainCode;
        }

        if ($messages !== []) {
            return new Err(new DomainValidationError($messages));
        }

        return new Ok([
            'codes' => $codes,
            'plainCodes' => $plainCodes,
        ]);
    }
}

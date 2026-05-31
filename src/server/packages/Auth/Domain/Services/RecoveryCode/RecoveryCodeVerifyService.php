<?php

declare(strict_types=1);

namespace Auth\Domain\Services\RecoveryCode;

use AdminUser\Domain\Models\AdminUserId;
use Auth\Domain\Models\RecoveryCode\RecoveryCode;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use SensitiveParameter;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;

class RecoveryCodeVerifyService
{
    public function __construct(
        private readonly RecoveryCodeHasherInterface $recoveryCodeHasher,
        private readonly RecoveryCodeRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<RecoveryCode, DomainError>
     */
    public function verify(#[SensitiveParameter] string $plainCode, AdminUserId $adminUserId): Result
    {
        $codes = $this->repository->findUnusedByAdminUserIdForUpdate($adminUserId);

        foreach ($codes as $code) {
            if (! $this->recoveryCodeHasher->verify($plainCode, $code->code->value)) {
                continue;
            }

            if (! $code->isAvailable()) {
                return new Err(new BusinessRuleViolationError('recovery_code_not_found'));
            }

            return new Ok($code);
        }

        return new Err(new BusinessRuleViolationError('recovery_code_not_found'));
    }
}

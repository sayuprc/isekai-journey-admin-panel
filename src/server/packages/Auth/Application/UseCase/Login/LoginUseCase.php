<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\Login;

use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class LoginUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private RefreshTokenIssueService $refreshTokenIssueService,
        private AccessTokenIssueService $accessTokenIssueService,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<LoginOutputData, UseCaseError>
     */
    public function handle(LoginInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->refreshTokenIssueService->issue($inputData->adminUserId);

            if ($result->isErr()) {
                return new Err($this->handleError($result->unwrapErr()));
            }

            ['token' => $refreshToken, 'plainToken' => $plainToken] = $result->unwrap();

            $accessToken = $this->accessTokenIssueService->issue($refreshToken->refreshTokenId->value);

            $this->refreshTokenRepository->save($refreshToken);

            $this->recorder->record(
                AuditAction::Login,
                AuditTargetType::AdminUser,
                $refreshToken->adminUserId,
                [
                    'refreshTokenId' => $refreshToken->refreshTokenId->value,
                ],
                $refreshToken->adminUserId,
            );

            return new Ok(new LoginOutputData($accessToken, $refreshToken->refreshTokenId->value, $plainToken));
        });
    }

    private function handleError(DomainError $error): UseCaseError
    {
        return match (true) {
            $error instanceof DomainValidationError => new InvalidInputError($error->errors),
            $error instanceof EntityRuleViolationError => new InvalidInputError([$error->field => [$error->message]]),
            default => throw new LogicException('予期しないドメインエラーが発生しました: ' . $error::class),
        };
    }
}

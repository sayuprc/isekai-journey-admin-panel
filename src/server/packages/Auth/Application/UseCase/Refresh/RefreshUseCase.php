<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\Refresh;

use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class RefreshUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private RefreshTokenIssueService $refreshTokenIssueService,
        private AccessTokenIssueService $accessTokenIssueService,
        private TokenHasherInterface $tokenHasher,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<RefreshOutputData, UseCaseError>
     */
    public function handle(RefreshInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $refreshTokenId = RefreshTokenId::create($inputData->refreshTokenId);

            if ($refreshTokenId->isErr()) {
                return new Err(new InvalidInputError([$refreshTokenId->unwrapErr()->field => [$refreshTokenId->unwrapErr()->message]]));
            }

            $refreshToken = $this->refreshTokenRepository->findActive($refreshTokenId->unwrap());

            if (is_null($refreshToken)) {
                return new Err(new AuthenticationError());
            }

            if (! $this->tokenHasher->verify($inputData->refreshToken, $refreshToken->token->value)) {
                return new Err(new AuthenticationError());
            }

            $issued = $this->refreshTokenIssueService->issue($refreshToken->adminUserId->value);

            if ($issued->isErr()) {
                return new Err(new AuthenticationError());
            }

            ['token' => $nextRefreshToken, 'plainToken' => $plainToken] = $issued->unwrap();

            $this->refreshTokenRepository->save($refreshToken->consume());
            $this->refreshTokenRepository->save($nextRefreshToken);

            $this->recorder->record(
                AuditAction::Refresh,
                AuditTargetType::AdminUser,
                $refreshToken->adminUserId,
                [
                    'refresh_token_id' => $nextRefreshToken->refreshTokenId->value,
                ],
                $refreshToken->adminUserId,
            );

            return new Ok(
                new RefreshOutputData(
                    $this->accessTokenIssueService->issue($nextRefreshToken->refreshTokenId->value),
                    $nextRefreshToken->refreshTokenId->value,
                    $plainToken,
                ),
            );
        });
    }
}

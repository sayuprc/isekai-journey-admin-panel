<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\LoginFinish;

use AdminUser\Domain\Models\AdminUserId;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\UseCaseError;
use Throwable;

readonly class LoginFinishUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private ClockInterface $clock,
        private AdminUserPasskeyRepositoryInterface $passkeyRepository,
        private PasskeyAuthenticatorInterface $passkeyAuthenticator,
        private PasskeyCeremonyStoreInterface $ceremonyStore,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private RefreshTokenIssueService $refreshTokenIssueService,
        private AccessTokenIssueService $accessTokenIssueService,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<LoginFinishOutputData, UseCaseError>
     */
    public function handle(LoginFinishInputData $inputData): Result
    {
        $state = $this->ceremonyStore->pull($inputData->authCeremonyId);

        if ($state === null || $state->type !== 'login' || $state->adminUserId === null) {
            return new Err(new AuthenticationError());
        }

        $credentialId = isset($inputData->credential['id']) && is_string($inputData->credential['id'])
            ? $inputData->credential['id']
            : null;

        if ($credentialId === null) {
            return new Err(new AuthenticationError());
        }

        $passkey = $this->passkeyRepository->findByCredentialId($credentialId);

        if ($passkey === null || $passkey->adminUserId !== $state->adminUserId) {
            return new Err(new AuthenticationError());
        }

        try {
            $verificationResult = $this->passkeyAuthenticator->finishAuthentication(
                $inputData->credential,
                $state->optionsJson,
                $passkey,
                $state->adminUserId,
            );
        } catch (Throwable) {
            return new Err(new AuthenticationError());
        }

        return $this->transaction->scope(function () use ($state, $passkey, $verificationResult): Result {
            $this->passkeyRepository->update($passkey->withCounter($verificationResult->signCount, $this->clock->now()));

            $refreshResult = $this->refreshTokenIssueService->issue($state->adminUserId);
            if ($refreshResult->isErr()) {
                return new Err(new AuthenticationError());
            }

            ['token' => $refreshToken, 'plainToken' => $plainToken] = $refreshResult->unwrap();

            $accessToken = $this->accessTokenIssueService->issue($refreshToken->refreshTokenId->value);

            $this->refreshTokenRepository->save($refreshToken);
            $this->recorder->record(
                AuditAction::Login,
                AuditTargetType::AdminUser,
                AdminUserId::reconstruct($state->adminUserId),
                ['refresh_token_id' => $refreshToken->refreshTokenId->value],
                AdminUserId::reconstruct($state->adminUserId),
            );

            return new Ok(new LoginFinishOutputData($accessToken, $refreshToken->refreshTokenId->value, $plainToken));
        });
    }
}

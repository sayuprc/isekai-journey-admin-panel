<?php

declare(strict_types=1);

namespace Auth\Application\Interactors;

use Auth\Application\UseCase\Login\LoginInputData;
use Auth\Application\UseCase\Login\LoginOutputData;
use Auth\Application\UseCase\Login\LoginUseCaseInterface;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Credential\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Credential\RefreshToken\RefreshTokenIssueService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;

readonly class LoginInteractor implements LoginUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private RefreshTokenIssueService $refreshTokenIssueService,
        private AccessTokenIssueService $accessTokenIssueService,
    ) {
    }

    public function handle(LoginInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->refreshTokenIssueService->issue($inputData->userId);

            if ($result->isErr()) {
                // TODO エラーハンドリング強化
                return new Err('');
            }

            $refreshToken = $result->unwrap();

            $accessToken = $this->accessTokenIssueService->issue($refreshToken->refreshTokenId->value);

            $this->refreshTokenRepository->save($refreshToken);

            return new Ok(new LoginOutputData($accessToken, $refreshToken));
        });
    }
}

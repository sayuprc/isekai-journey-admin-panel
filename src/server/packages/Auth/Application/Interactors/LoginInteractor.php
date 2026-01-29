<?php

declare(strict_types=1);

namespace Auth\Application\Interactors;

use Auth\Application\UseCase\Login\LoginInputData;
use Auth\Application\UseCase\Login\LoginOutputData;
use Auth\Application\UseCase\Login\LoginUseCaseInterface;
use Auth\Domain\Models\Credential\AccessToken\AccessTokenFactoryInterface;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenFactoryInterface;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;

readonly class LoginInteractor implements LoginUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private RefreshTokenFactoryInterface $refreshTokenFactory,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private AccessTokenFactoryInterface $accessTokenFactory,
    ) {
    }

    public function handle(LoginInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $refreshTokenResult = $this->refreshTokenFactory->create($inputData->userId);

            if ($refreshTokenResult->isErr()) {
                // TODO エラーハンドリング強化
                return new Err('');
            }

            $refreshToken = $refreshTokenResult->unwrap();

            $accessTokenResult = $this->accessTokenFactory->create($refreshToken->refreshTokenId->value);

            if ($accessTokenResult->isErr()) {
                // TODO エラーハンドリング強化
                return new Err('');
            }

            $accessToken = $accessTokenResult->unwrap();

            $this->refreshTokenRepository->save($refreshToken);

            return new Ok(new LoginOutputData($accessToken, $refreshToken));
        });
    }
}

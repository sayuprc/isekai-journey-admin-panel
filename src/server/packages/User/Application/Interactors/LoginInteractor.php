<?php

declare(strict_types=1);

namespace User\Application\Interactors;

use ResultType\Eager\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use User\Application\UseCase\Login\LoginInputData;
use User\Application\UseCase\Login\LoginOutputData;
use User\Application\UseCase\Login\LoginUseCaseInterface;
use User\Domain\Models\Credential\AccessToken\AccessTokenFactoryInterface;
use User\Domain\Models\Credential\RefreshToken\RefreshTokenFactoryInterface;
use User\Domain\Models\Credential\RefreshToken\RefreshTokenRepositoryInterface;

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
            $refreshToken = $this->refreshTokenFactory->create($inputData->userId);
            $accessToken = $this->accessTokenFactory->create($refreshToken->refreshTokenId->value);

            $this->refreshTokenRepository->save($refreshToken);

            return new Ok(new LoginOutputData($accessToken, $refreshToken));
        });
    }
}

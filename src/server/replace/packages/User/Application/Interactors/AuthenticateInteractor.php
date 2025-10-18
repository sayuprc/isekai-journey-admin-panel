<?php

declare(strict_types=1);

namespace User\Application\Interactors;

use ResultType\Eager\Err;
use ResultType\Eager\Ok;
use ResultType\Result;
use User\Application\UseCase\Authenticate\AuthenticateInputData;
use User\Application\UseCase\Authenticate\AuthenticateOutputData;
use User\Application\UseCase\Authenticate\AuthenticateUseCaseInterface;
use User\Domain\Models\Credential\RefreshToken\RefreshTokenId;
use User\Domain\Models\Credential\RefreshToken\RefreshTokenRepositoryInterface;
use User\Domain\Models\UserRepositoryInterface;
use User\Domain\Services\Credential\AccessToken\Exceptions\ExpiredException;
use User\Domain\Services\Credential\AccessToken\Exceptions\InvalidIssuerException;
use User\Domain\Services\Credential\AccessToken\JwtHandlerInterface;

readonly class AuthenticateInteractor implements AuthenticateUseCaseInterface
{
    public function __construct(
        private JwtHandlerInterface $jwtHandler,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function handle(AuthenticateInputData $inputData): Result
    {
        try {
            $payload = $this->jwtHandler->verify($inputData->accessToken);
        } catch (ExpiredException $e) {
            return new Err('JWTの有効期限が切れている');
        } catch (InvalidIssuerException $e) {
            return new Err('不正なIssuer');
        }

        $foundRefreshToken = $this->refreshTokenRepository->findActive(new RefreshTokenId($payload->jti));

        if (is_null($foundRefreshToken)) {
            return new Err(sprintf('リフレッシュトークンが見つからない [refreshTokenId: %s]', $payload->jti));
        }

        $foundUser = $this->userRepository->find($foundRefreshToken->userId);

        if (is_null($foundUser)) {
            return new Err(sprintf('ユーザーが見つからない [userId: %s]', $foundRefreshToken->userId->value));
        }

        return new Ok(new AuthenticateOutputData());
    }
}

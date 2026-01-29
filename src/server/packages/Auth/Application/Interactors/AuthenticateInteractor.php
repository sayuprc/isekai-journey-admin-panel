<?php

declare(strict_types=1);

namespace Auth\Application\Interactors;

use Auth\Application\UseCase\Authenticate\AuthenticateInputData;
use Auth\Application\UseCase\Authenticate\AuthenticateOutputData;
use Auth\Application\UseCase\Authenticate\AuthenticateUseCaseInterface;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Credential\AccessToken\Exceptions\ExpiredException;
use Auth\Domain\Services\Credential\AccessToken\Exceptions\InvalidIssuerException;
use Auth\Domain\Services\Credential\AccessToken\JwtHandlerInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use User\Domain\Models\UserRepositoryInterface;

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

        return RefreshTokenId::create($payload->jti)
            // TODO エラーハンドリング強化
            ->mapErr(fn (): string => '')
            ->andThen(function (RefreshTokenId $refreshTokenId): Result {
                $foundRefreshToken = $this->refreshTokenRepository->findActive($refreshTokenId);

                if (is_null($foundRefreshToken)) {
                    return new Err(sprintf('リフレッシュトークンが見つからない [refreshTokenId: %s]', $refreshTokenId->value));
                }

                $foundUser = $this->userRepository->find($foundRefreshToken->userId);

                if (is_null($foundUser)) {
                    return new Err(sprintf('ユーザーが見つからない [userId: %s]', $foundRefreshToken->userId->value));
                }

                return new Ok(new AuthenticateOutputData());
            });
    }
}

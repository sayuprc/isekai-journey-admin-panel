<?php

declare(strict_types=1);

namespace Auth\Application\Interactors;

use Auth\Application\UseCase\Login\LoginInputData;
use Auth\Application\UseCase\Login\LoginOutputData;
use Auth\Application\UseCase\Login\LoginUseCaseInterface;
use Auth\Domain\Models\Credential\AccessToken\AccessTokenFactoryInterface;
use Auth\Domain\Models\Credential\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Credential\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Credential\RefreshToken\RefreshToken;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenFactoryInterface;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Models\Credential\RefreshToken\TokenValue;
use Auth\Domain\Services\Credential\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Credential\AccessToken\JwtConfigInterface;
use Auth\Domain\Services\Credential\RefreshToken\RandomTokenGeneratorInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Support\Contracts\TransactionInterface;
use Support\Contracts\UuidGeneratorInterface;
use User\Domain\Models\UserId;

readonly class LoginInteractor implements LoginUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private RefreshTokenFactoryInterface $refreshTokenFactory,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private AccessTokenFactoryInterface $accessTokenFactory,
        private UuidGeneratorInterface $uuidGenerator,
        private RandomTokenGeneratorInterface $randomTokenGenerator,
        private ClockInterface $clock,
        private JwtConfigInterface $jwtConfig,
    ) {
    }

    public function handle(LoginInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $refreshTokenResult = Result::collect4(
                RefreshTokenId::create($this->uuidGenerator->generate()),
                UserId::create($inputData->userId),
                TokenValue::create($this->randomTokenGenerator->generate()),
                ExpiredAt::create($this->clock->now()->modify('+7 days')),
            )->map(fn (array $values): RefreshToken => $this->refreshTokenFactory->create(...[...$values, ConsumptionStatus::Unused]));

            if ($refreshTokenResult->isErr()) {
                // TODO エラーハンドリング強化
                return new Err('');
            }

            $refreshToken = $refreshTokenResult->unwrap();

            $now = $this->clock->now();

            $payload = new AccessTokenPayload(
                iss: $this->jwtConfig->issuer(),
                iat: $now->getTimestamp(),
                exp: $now->modify('+1 hours')->getTimestamp(),
                nbf: $now->getTimestamp(),
                jti: $refreshToken->refreshTokenId->value,
            );

            $accessToken = $this->accessTokenFactory->create($payload);

            $this->refreshTokenRepository->save($refreshToken);

            return new Ok(new LoginOutputData($accessToken, $refreshToken));
        });
    }
}

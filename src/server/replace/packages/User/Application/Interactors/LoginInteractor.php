<?php

declare(strict_types=1);

namespace User\Application\Interactors;

use ResultType\Eager\Err;
use ResultType\Eager\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use User\Application\UseCase\Login\LoginInputData;
use User\Application\UseCase\Login\LoginOutputData;
use User\Application\UseCase\Login\LoginUseCaseInterface;
use User\Domain\Models\Credential\AccessToken\AccessToken;
use User\Domain\Models\Credential\AccessToken\AccessTokenFactoryInterface;
use User\Domain\Models\Credential\RefreshToken\RefreshToken;
use User\Domain\Models\Credential\RefreshToken\RefreshTokenFactoryInterface;
use User\Domain\Models\Credential\RefreshToken\RefreshTokenRepositoryInterface;
use User\Domain\Models\Email;
use User\Domain\Models\User;
use User\Domain\Models\UserRepositoryInterface;
use User\Domain\Services\HasherInterface;

readonly class LoginInteractor implements LoginUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private UserRepositoryInterface $userRepository,
        private HasherInterface $hasher,
        private RefreshTokenFactoryInterface $refreshTokenFactory,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private AccessTokenFactoryInterface $accessTokenFactory,
    ) {
    }

    public function handle(LoginInputData $inputData): Result
    {
        return $this->transaction->scope(
            fn (): Result => $this->authenticate($inputData)->match(
                fn (User $user): Result => new Ok(new LoginOutputData(...$this->issueCredentials($user))),
                fn (string $message): Result => new Err($message),
            )
        );
    }

    /**
     * @return Result<User, string>
     */
    private function authenticate(LoginInputData $inputData): Result
    {
        $foundUser = $this->userRepository->findByEmail(new Email($inputData->email));

        return is_null($foundUser) || ! $this->hasher->check($inputData->password, $foundUser->hashedPassword->value)
            ? new Err('認証失敗')
            : new Ok($foundUser);
    }

    /**
     * @return array{0: AccessToken, 1: RefreshToken}
     */
    private function issueCredentials(User $user): array
    {
        $refreshToken = $this->refreshTokenFactory->create($user->userId->value);
        $accessToken = $this->accessTokenFactory->create($refreshToken->refreshTokenId->value);

        $this->refreshTokenRepository->insert($refreshToken);

        return [$accessToken, $refreshToken];
    }
}

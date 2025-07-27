<?php

declare(strict_types=1);

namespace User\Application\Interactors;

use ResultType\Eager\Err;
use ResultType\Eager\Ok;
use ResultType\Result;
use User\Application\UseCase\Authenticate\AuthenticateInputData;
use User\Application\UseCase\Authenticate\AuthenticateOutputData;
use User\Application\UseCase\Authenticate\AuthenticateUseCaseInterface;
use User\Domain\Models\Credential\CredentialId;
use User\Domain\Models\Credential\CredentialRepositoryInterface;
use User\Domain\Models\UserRepositoryInterface;
use User\Domain\Services\Jwt\Exceptions\ExpiredException;
use User\Domain\Services\Jwt\Exceptions\InvalidIssuerException;
use User\Domain\Services\Jwt\JwtHandlerInterface;

readonly class AuthenticateInteractor implements AuthenticateUseCaseInterface
{
    public function __construct(
        private JwtHandlerInterface $jwtHandler,
        private CredentialRepositoryInterface $credentialRepository,
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

        $foundCredential = $this->credentialRepository->findActive(new CredentialId($payload->jti));

        if (is_null($foundCredential)) {
            return new Err(sprintf('Credentialが見つからない [credentialId: %s]', $payload->jti));
        }

        $foundUser = $this->userRepository->find($foundCredential->userId);

        if (is_null($foundUser)) {
            return new Err(sprintf('ユーザーが見つからない [userId: %s]', $foundCredential->userId->value));
        }

        return new Ok(new AuthenticateOutputData());
    }
}

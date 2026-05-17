<?php

declare(strict_types=1);

namespace AdminUser\Application\Admin\UseCase\Register;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\HashedPassword;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use AdminUser\Domain\Services\HasherInterface;
use AdminUser\Domain\Services\RegistrationToken\RegistrationTokenConsumeService;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class RegisterUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private HasherInterface $hasher,
        private AdminUserRepositoryInterface $adminUserRepository,
        private AdminUserIntegrityService $integrityService,
        private RegistrationTokenConsumeService $consumeService,
        private RegistrationTokenRepositoryInterface $registrationTokenRepository,
        private RefreshTokenIssueService $refreshTokenIssueService,
        private AccessTokenIssueService $accessTokenIssueService,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
    ) {
    }

    /**
     * @return Result<RegisterOutputData, UseCaseError>
     */
    public function handle(RegisterInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $emailResult = Email::create($inputData->email);

            if ($emailResult->isErr()) {
                return new Err($this->handleError($emailResult->unwrapErr()));
            }

            $tokenResult = $this->consumeService->verify($inputData->plainToken, $emailResult->unwrap());

            if ($tokenResult->isErr()) {
                return new Err($this->handleError($tokenResult->unwrapErr()));
            }

            $token = $tokenResult->unwrap();

            $adminUserResult = $this->integrityService->prepareForCreate(
                $inputData->name,
                $token->email->value,
                $token->role->value,
                $token->permissions->toArray(),
            );

            if ($adminUserResult->isErr()) {
                return new Err($this->handleError($adminUserResult->unwrapErr()));
            }

            $passwordResult = HashedPassword::create($this->hasher->hash($inputData->plainPassword));

            if ($passwordResult->isErr()) {
                return new Err($this->handleError($passwordResult->unwrapErr()));
            }

            $adminUser = $this->adminUserRepository->register($adminUserResult->unwrap(), $passwordResult->unwrap());

            $this->registrationTokenRepository->save($token->consume());

            $refreshTokenResult = $this->refreshTokenIssueService->issue($adminUser->adminUserId->value);

            if ($refreshTokenResult->isErr()) {
                return new Err($this->handleError($refreshTokenResult->unwrapErr()));
            }

            ['token' => $refreshToken, 'plainToken' => $plainRefreshToken] = $refreshTokenResult->unwrap();

            $accessToken = $this->accessTokenIssueService->issue($refreshToken->refreshTokenId->value);

            $this->refreshTokenRepository->save($refreshToken);

            return new Ok(new RegisterOutputData(
                $accessToken,
                $refreshToken->refreshTokenId->value,
                $plainRefreshToken,
            ));
        });
    }

    private function handleError(DomainError $error): UseCaseError
    {
        return match (true) {
            $error instanceof DomainValidationError => new InvalidInputError($error->errors),
            $error instanceof EntityRuleViolationError => new InvalidInputError([$error->field => [$error->message]]),
            $error instanceof BusinessRuleViolationError => new BusinessLogicError($error->message),
            default => throw new LogicException('予期しないドメインエラーが発生しました: ' . $error::class),
        };
    }
}

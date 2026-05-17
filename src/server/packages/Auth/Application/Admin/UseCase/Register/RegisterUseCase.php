<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Register;

use AdminUser\Application\Service\RegisterAdminUserService;
use AdminUser\Domain\Models\Email;
use Auth\Domain\Models\AdminRegistrationToken\AdminRegistrationToken;
use Auth\Domain\Models\AdminRegistrationToken\AdminRegistrationTokenRepositoryInterface;
use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class RegisterUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private AdminRegistrationTokenRepositoryInterface $tokenRepository,
        private TokenHasherInterface $tokenHasher,
        private RegisterAdminUserService $registerAdminUserService,
    ) {
    }

    /**
     * @return Result<null, UseCaseError>
     */
    public function handle(RegisterInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            if (mb_trim($inputData->registrationToken) === '') {
                return new Err(new InvalidInputError([
                    'registrationToken' => ['登録トークンを入力してください'],
                ]));
            }

            $emailResult = Email::create($inputData->email);

            if ($emailResult->isErr()) {
                /** @var EntityRuleViolationError $error */
                $error = $emailResult->unwrapErr();

                return new Err(new InvalidInputError([
                    'email' => [$error->message],
                ]));
            }

            $email = $emailResult->unwrap();
            $matchedToken = $this->findMatchedToken($email, $inputData->registrationToken);

            if (is_null($matchedToken)) {
                return new Err(new BusinessLogicError('登録トークンが無効です'));
            }

            $registerResult = $this->registerAdminUserService->handle(
                $inputData->name,
                $inputData->email,
                $inputData->password,
                $matchedToken->role->value,
                [],
            );

            if ($registerResult->isErr()) {
                return new Err($registerResult->unwrapErr());
            }

            $this->tokenRepository->save($matchedToken->consume());

            return new Ok(null);
        });
    }

    private function findMatchedToken(Email $email, string $plainToken): ?AdminRegistrationToken
    {
        foreach ($this->tokenRepository->findAvailableByEmail($email) as $candidate) {
            if ($this->tokenHasher->verify($plainToken, $candidate->token->value)) {
                return $candidate;
            }
        }

        return null;
    }
}

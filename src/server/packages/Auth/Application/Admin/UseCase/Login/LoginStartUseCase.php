<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Login;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\PasskeyCeremonyType;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class LoginStartUseCase
{
    public function __construct(
        private AdminUserRepositoryInterface $adminUserRepository,
        private AdminUserPasskeyRepositoryInterface $passkeyRepository,
        private PasskeyAuthenticatorInterface $passkeyAuthenticator,
        private PasskeyCeremonyStoreInterface $ceremonyStore,
        private UuidGeneratorInterface $uuidGenerator,
    ) {
    }

    /**
     * @return Result<LoginStartOutputData, UseCaseError>
     */
    public function handle(LoginStartInputData $inputData): Result
    {
        $emailResult = Email::create($inputData->email);

        if ($emailResult->isErr()) {
            return new Err($this->handleError($emailResult->unwrapErr()));
        }

        $email = $emailResult->unwrap();
        $adminUser = $this->adminUserRepository->findByEmail($email);
        $passkeys = is_null($adminUser)
            ? []
            : $this->passkeyRepository->findByAdminUserId($adminUser->adminUserId->value);

        // ユーザー列挙を防ぐため、メールの実在やパスキー登録の有無に依らず常に同一形状の
        // ceremony を返す。実在ユーザーのみ本物の adminUserId を束縛し、それ以外はダミーの
        // adminUserId にすることで finish 時に必ず認証失敗となる (応答は区別できない)。
        $adminUserId = is_null($adminUser) || $passkeys === []
            ? $this->uuidGenerator->generate()
            : $adminUser->adminUserId->value;

        $authCeremonyId = $this->uuidGenerator->generate();
        $startResult = $this->passkeyAuthenticator->startAuthentication();

        $this->ceremonyStore->put(new PasskeyCeremonyState(
            $authCeremonyId,
            PasskeyCeremonyType::Login,
            $email->value,
            null,
            $adminUserId,
            $startResult->optionsJson,
        ));

        return new Ok(new LoginStartOutputData($authCeremonyId, $startResult->publicKey));
    }

    private function handleError(DomainError $error): UseCaseError
    {
        return match (true) {
            $error instanceof DomainValidationError => new InvalidInputError($error->errors),
            $error instanceof EntityRuleViolationError => new InvalidInputError([$error->field => [$error->message]]),
            default => throw new LogicException('予期しないドメインエラーが発生しました: ' . $error::class),
        };
    }
}

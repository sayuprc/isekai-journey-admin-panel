<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Recovery;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\PasskeyCeremonyType;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyUserHandleGeneratorInterface;
use Auth\Domain\Services\RecoveryCode\RecoveryCodeVerifyService;
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

readonly class RecoveryStartUseCase
{
    public function __construct(
        private AdminUserRepositoryInterface $adminUserRepository,
        private RecoveryCodeVerifyService $verifyService,
        private PasskeyAuthenticatorInterface $passkeyAuthenticator,
        private PasskeyUserHandleGeneratorInterface $userHandleGenerator,
        private PasskeyCeremonyStoreInterface $ceremonyStore,
        private UuidGeneratorInterface $uuidGenerator,
    ) {
    }

    /**
     * @return Result<RecoveryStartOutputData, UseCaseError>
     */
    public function handle(RecoveryStartInputData $inputData): Result
    {
        $emailResult = Email::create($inputData->email);

        if ($emailResult->isErr()) {
            return new Err($this->handleError($emailResult->unwrapErr()));
        }

        $email = $emailResult->unwrap();
        $adminUser = $this->adminUserRepository->findByEmail($email);

        // ユーザー列挙を防ぐため、メールの実在やコードの正否に依らず常に同一形状の
        // 登録 ceremony を返す。実在ユーザーかつコード検証成功時のみ本物の adminUserId と
        // 検証済みコードの id を束縛し、それ以外はダミーの adminUserId かつ recoveryCodeId
        // は null とすることで finish 時に必ず失敗させる。
        // コードの消費は ceremony 完走時 (RecoveryFinish) に確定するためここでは検証のみ行う。
        $adminUserId = $this->uuidGenerator->generate();
        $recoveryCodeId = null;

        if (! is_null($adminUser)) {
            $verifyResult = $this->verifyService->verify($inputData->plainCode, $adminUser->adminUserId);

            if ($verifyResult->isOk()) {
                $adminUserId = $adminUser->adminUserId->value;
                $recoveryCodeId = $verifyResult->unwrap()->recoveryCodeId->value;
            }
        }

        $authCeremonyId = $this->uuidGenerator->generate();
        $startResult = $this->passkeyAuthenticator->startRegistration(
            $this->userHandleGenerator->generate(),
            $email->value,
            $inputData->name,
        );

        $this->ceremonyStore->put(new PasskeyCeremonyState(
            $authCeremonyId,
            PasskeyCeremonyType::Recovery,
            $email->value,
            $inputData->name,
            $adminUserId,
            $startResult->optionsJson,
            $recoveryCodeId,
        ));

        return new Ok(new RecoveryStartOutputData($authCeremonyId, $startResult->publicKey));
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

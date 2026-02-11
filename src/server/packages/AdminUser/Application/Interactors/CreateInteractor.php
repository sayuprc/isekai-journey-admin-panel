<?php

declare(strict_types=1);

namespace AdminUser\Application\Interactors;

use AdminUser\Application\UseCase\Create\CreateInputData;
use AdminUser\Application\UseCase\Create\CreateOutputData;
use AdminUser\Application\UseCase\Create\CreateUseCaseInterface;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;

readonly class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private AdminUserRepositoryInterface $repository,
        private AdminUserIntegrityService $service,
    ) {
    }

    public function handle(CreateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->prepareForCreate($inputData->email, $inputData->plainPassword);

            if ($result->isErr()) {
                return new Err($result->unwrapErr());
            }

            $user = $result->unwrap();

            $this->repository->save($user);

            return new Ok(new CreateOutputData($user));
        });
    }
}

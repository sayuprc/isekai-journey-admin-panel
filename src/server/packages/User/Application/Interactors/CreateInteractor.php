<?php

declare(strict_types=1);

namespace User\Application\Interactors;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use User\Application\UseCase\Create\CreateInputData;
use User\Application\UseCase\Create\CreateOutputData;
use User\Application\UseCase\Create\CreateUseCaseInterface;
use User\Domain\Models\UserRepositoryInterface;
use User\Domain\Services\UserIntegrityService;

readonly class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private UserRepositoryInterface $repository,
        private UserIntegrityService $service,
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

<?php

declare(strict_types=1);

namespace User\Application\Interactors;

use ResultType\Eager\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use User\Application\UseCase\Login\LoginInputData;
use User\Application\UseCase\Login\LoginOutputData;
use User\Application\UseCase\Login\LoginUseCaseInterface;
use User\Domain\Models\Credential\CredentialFactoryInterface;
use User\Domain\Models\Credential\CredentialRepositoryInterface;

readonly class LoginInteractor implements LoginUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private CredentialFactoryInterface $factory,
        private CredentialRepositoryInterface $repository,
    ) {
    }

    public function handle(LoginInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $credential = $this->factory->create($inputData->userId);

            $this->repository->insert($credential);

            return new Ok(new LoginOutputData($credential));
        });
    }
}

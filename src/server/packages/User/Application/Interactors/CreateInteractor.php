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
use User\Domain\Models\Email;
use User\Domain\Models\UserFactoryInterface;
use User\Domain\Models\UserRepositoryInterface;

readonly class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private UserRepositoryInterface $repository,
        private UserFactoryInterface $factory,
    ) {
    }

    public function handle(CreateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            if (! is_null($this->repository->findByEmail(new Email($inputData->email)))) {
                return new Err("User already exists: {$inputData->email}");
            }

            $user = $this->factory->create($inputData->email, $inputData->plainPassword);

            $this->repository->save($user);

            return new Ok(new CreateOutputData($user));
        });
    }
}

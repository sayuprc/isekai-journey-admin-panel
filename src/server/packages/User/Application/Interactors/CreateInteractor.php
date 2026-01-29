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
use User\Domain\Models\User;
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
        return $this->factory->create($inputData->email, $inputData->plainPassword)
            // TODO エラーハンドリング強化
            ->mapErr(fn (): string => '')
            ->andThen(function (User $user): Result {
                return $this->transaction->scope(function () use ($user): Result {
                    if (! is_null($this->repository->findByEmail($user->email))) {
                        return new Err("User already exists: {$user->email->value}");
                    }

                    $this->repository->save($user);

                    return new Ok(new CreateOutputData($user));
                });
            });
    }
}

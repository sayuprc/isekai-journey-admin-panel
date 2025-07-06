<?php

declare(strict_types=1);

namespace User\Application\Interactors;

use ResultType\Eager\Err;
use ResultType\Eager\Ok;
use ResultType\Result;
use User\Application\UseCase\Create\CreateInputData;
use User\Application\UseCase\Create\CreateOutputData;
use User\Application\UseCase\Create\CreateUseCaseInterface;
use User\Domain\Models\Email;
use User\Domain\Models\UserFactoryInterface;
use User\Domain\Models\UserRepositoryInterface;

class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly UserFactoryInterface $factory,
    ) {
    }

    public function handle(CreateInputData $inputData): Result
    {
        if (! is_null($this->repository->findByEmail(new Email($inputData->email)))) {
            return new Err("User already exists: {$inputData->email}");
        }

        $user = $this->factory->create($inputData->email, $inputData->plainPassword);

        $this->repository->insert($user);

        return new Ok(new CreateOutputData($user));
    }
}

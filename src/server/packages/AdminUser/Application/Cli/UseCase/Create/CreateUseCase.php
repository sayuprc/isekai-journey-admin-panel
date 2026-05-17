<?php

declare(strict_types=1);

namespace AdminUser\Application\Cli\UseCase\Create;

use AdminUser\Application\Service\RegisterAdminUserService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\UseCase\Error\UseCaseError;

readonly class CreateUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private RegisterAdminUserService $service,
    ) {
    }

    /**
     * @return Result<CreateOutputData, UseCaseError>
     */
    public function handle(CreateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->handle(
                $inputData->name,
                $inputData->email,
                $inputData->plainPassword,
                $inputData->role,
                $inputData->permissions,
            );

            if ($result->isErr()) {
                return new Err($result->unwrapErr());
            }

            return new Ok(new CreateOutputData($result->unwrap()));
        });
    }
}

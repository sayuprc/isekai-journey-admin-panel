<?php

declare(strict_types=1);

namespace Song\Application\Interactors;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\Assemble\SongAssembler;
use Song\Application\UseCase\Create\CreateInputData;
use Song\Application\UseCase\Create\CreateOutputData;
use Song\Application\UseCase\Create\CreateUseCaseInterface;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Services\SongIntegrityService;
use Support\Contracts\TransactionInterface;

readonly class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private SongRepositoryInterface $repository,
        private SongIntegrityService $service,
        private SongAssembler $assembler,
    ) {
    }

    public function handle(CreateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->prepareForCreate(
                $inputData->title,
                $inputData->description,
                $inputData->songTypeValue,
                $inputData->orderNo,
                $inputData->arrangers,
                $inputData->composers,
                $inputData->lyricists,
            );

            if ($result->isErr()) {
                return new Err($result->unwrapErr());
            }

            $song = $result->unwrap();

            $this->repository->save($song);

            return new Ok(new CreateOutputData($this->assembler->assemble($song)));
        });
    }
}

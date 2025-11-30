<?php

declare(strict_types=1);

namespace Performer\Application\Interactors;

use Performer\Application\UseCase\Delete\DeleteInputData;
use Performer\Application\UseCase\Delete\DeleteUseCaseInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerRepositoryInterface;

readonly class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(private PerformerRepositoryInterface $repository)
    {
    }

    public function handle(DeleteInputData $inputData): void
    {
        $this->repository->delete(new PerformerId($inputData->performerId));
    }
}

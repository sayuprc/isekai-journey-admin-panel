<?php

declare(strict_types=1);

namespace Song\Application\Interactors;

use Song\Application\Assemble\SongAssembler;
use Song\Application\UseCase\List\ListOutputData;
use Song\Application\UseCase\List\ListUseCaseInterface;
use Song\Domain\Models\SongRepositoryInterface;

readonly class ListInteractor implements ListUseCaseInterface
{
    public function __construct(
        private SongRepositoryInterface $repository,
        private SongAssembler $assembler,
    ) {
    }

    public function handle(): ListOutputData
    {
        return new ListOutputData(array_map($this->assembler->assemble(...), $this->repository->all()));
    }
}

<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Delete;

interface DeleteUseCaseInterface
{
    public function handle(DeleteInputData $inputData): void;
}

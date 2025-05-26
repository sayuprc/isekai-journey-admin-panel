<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\Delete;

interface DeleteUseCaseInterface
{
    public function handle(DeleteInputData $inputData): void;
}

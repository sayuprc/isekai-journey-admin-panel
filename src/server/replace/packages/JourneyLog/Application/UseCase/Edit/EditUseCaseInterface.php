<?php

declare(strict_types=1);

namespace JourneyLog\Application\UseCase\Edit;

interface EditUseCaseInterface
{
    public function handle(EditInputData $inputData): void;
}

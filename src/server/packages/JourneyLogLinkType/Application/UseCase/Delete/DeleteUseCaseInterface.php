<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\UseCase\Delete;

interface DeleteUseCaseInterface
{
    public function handle(DeleteInputData $inputData): void;
}

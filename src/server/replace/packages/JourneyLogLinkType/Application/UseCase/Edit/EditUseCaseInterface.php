<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\UseCase\Edit;

interface EditUseCaseInterface
{
    public function handle(EditInputData $inputData): void;
}

<?php

declare(strict_types=1);

namespace JourneyLogLinkType\UseCases\Edit;

interface EditUseCaseInterface
{
    public function handle(EditInputData $inputData): void;
}

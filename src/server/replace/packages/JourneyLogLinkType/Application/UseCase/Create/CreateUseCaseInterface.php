<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\UseCase\Create;

interface CreateUseCaseInterface
{
    public function handle(CreateInputData $inputData): void;
}

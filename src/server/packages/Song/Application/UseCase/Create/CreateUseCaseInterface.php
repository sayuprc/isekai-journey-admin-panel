<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Create;

interface CreateUseCaseInterface
{
    public function handle(CreateInputData $inputData): void;
}

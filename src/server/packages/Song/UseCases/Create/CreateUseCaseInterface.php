<?php

declare(strict_types=1);

namespace Song\UseCases\Create;

interface CreateUseCaseInterface
{
    public function handle(CreateInputData $inputData): void;
}

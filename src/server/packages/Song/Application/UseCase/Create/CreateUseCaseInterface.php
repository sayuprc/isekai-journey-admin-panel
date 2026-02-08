<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Create;

use ResultType\Result;

interface CreateUseCaseInterface
{
    /**
     * @return Result<CreateOutputData, string>
     */
    public function handle(CreateInputData $inputData): Result;
}

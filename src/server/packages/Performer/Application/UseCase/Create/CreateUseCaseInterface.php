<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\Create;

use ResultType\Result;

interface CreateUseCaseInterface
{
    /**
     * @return Result<CreateOutputData, string>
     */
    public function handle(CreateInputData $inputData): Result;
}

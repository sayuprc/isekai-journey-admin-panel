<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Get;

use ResultType\Result;

interface GetUseCaseInterface
{
    /**
     * @return Result<GetOutputData, string>
     */
    public function handle(GetInputData $inputData): Result;
}

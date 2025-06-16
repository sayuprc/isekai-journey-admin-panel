<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\Get;

use ResultType\Result;

interface GetUseCaseInterface
{
    /**
     * @return Result<GetOutputData, string>
     */
    public function handle(GetInputData $inputData): Result;
}

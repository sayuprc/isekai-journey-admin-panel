<?php

declare(strict_types=1);

namespace JourneyLogLinkType\UseCases\Get;

use Support\ResultType\Result;

interface GetUseCaseInterface
{
    /**
     * @return Result<GetOutputData, string>
     */
    public function handle(GetInputData $inputData): Result;
}

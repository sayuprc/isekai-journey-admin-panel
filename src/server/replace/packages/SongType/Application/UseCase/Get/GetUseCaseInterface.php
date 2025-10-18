<?php

declare(strict_types=1);

namespace SongType\Application\UseCase\Get;

use ResultType\Result;

interface GetUseCaseInterface
{
    /**
     * @return Result<GetOutputData, string>
     */
    public function handle(GetInputData $inputData): Result;
}

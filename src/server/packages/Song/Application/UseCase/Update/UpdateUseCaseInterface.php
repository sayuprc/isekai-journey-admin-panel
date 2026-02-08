<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Update;

use ResultType\Result;

interface UpdateUseCaseInterface
{
    /**
     * @return Result<UpdateOutputData, string>
     */
    public function handle(UpdateInputData $inputData): Result;
}

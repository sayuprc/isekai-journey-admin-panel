<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Delete;

use ResultType\Result;

interface DeleteUseCaseInterface
{
    /**
     * @return Result<null, string>
     */
    public function handle(DeleteInputData $inputData): Result;
}

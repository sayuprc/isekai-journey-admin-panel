<?php

declare(strict_types=1);

namespace Creator\UseCases\Edit;

use Support\ResultType\Result;

interface EditUseCaseInterface
{
    /**
     * @return Result<null, string>
     */
    public function handle(EditInputData $inputData): Result;
}

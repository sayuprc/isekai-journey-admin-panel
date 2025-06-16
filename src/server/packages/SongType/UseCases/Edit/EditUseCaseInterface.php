<?php

declare(strict_types=1);

namespace SongType\UseCases\Edit;

use ResultType\Result;

interface EditUseCaseInterface
{
    /**
     * @return Result<null, string>
     */
    public function handle(EditInputData $inputData): Result;
}

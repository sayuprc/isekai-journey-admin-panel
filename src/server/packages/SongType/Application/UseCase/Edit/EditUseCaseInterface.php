<?php

declare(strict_types=1);

namespace SongType\Application\UseCase\Edit;

use ResultType\Result;

interface EditUseCaseInterface
{
    /**
     * @return Result<null, string>
     */
    public function handle(EditInputData $inputData): Result;
}

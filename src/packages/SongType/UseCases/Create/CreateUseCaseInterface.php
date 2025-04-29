<?php

declare(strict_types=1);

namespace SongType\UseCases\Create;

use Support\ResultType\Result;

interface CreateUseCaseInterface
{
    /**
     * @return Result<null, string>
     */
    public function handle(CreateInputData $inputData): Result;
}

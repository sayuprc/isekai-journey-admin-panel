<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Create;

use ResultType\Result;

interface CreateUseCaseInterface
{
    /**
     * @return Result<null, string>
     */
    public function handle(CreateInputData $inputData): Result;
}

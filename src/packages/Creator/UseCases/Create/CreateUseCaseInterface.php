<?php

declare(strict_types=1);

namespace Creator\UseCases\Create;

use Support\ResultType\Result;

interface CreateUseCaseInterface
{
    /**
     * @return Result<null, string>
     */
    public function handle(CreateRequest $request): Result;
}

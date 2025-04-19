<?php

declare(strict_types=1);

namespace Creator\UseCases\Get;

use Support\Result\Result;

interface GetUseCaseInterface
{
    /**
     * @return Result<GetResponse, string>
     */
    public function handle(GetRequest $request): Result;
}

<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\Get;

use Support\ResultType\Result;

interface GetUseCaseInterface
{
    /**
     * @param GetRequest $request
     *
     * @return Result<GetResponse, string>
     */
    public function handle(GetRequest $request): Result;
}

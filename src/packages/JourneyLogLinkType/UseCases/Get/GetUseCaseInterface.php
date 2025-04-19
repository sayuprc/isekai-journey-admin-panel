<?php

declare(strict_types=1);

namespace JourneyLogLinkType\UseCases\Get;

use Support\ResultType\Result;

interface GetUseCaseInterface
{
    /**
     * @return Result<GetResponse, string>
     */
    public function handle(GetRequest $request): Result;
}

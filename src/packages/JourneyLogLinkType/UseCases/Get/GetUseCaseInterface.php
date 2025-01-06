<?php

declare(strict_types=1);

namespace JourneyLogLinkType\UseCases\Get;

interface GetUseCaseInterface
{
    public function handle(GetRequest $request): GetResponse;
}

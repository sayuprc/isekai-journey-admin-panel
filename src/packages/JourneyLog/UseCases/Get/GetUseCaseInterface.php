<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\Get;

interface GetUseCaseInterface
{
    public function handle(GetRequest $request): GetResponse;
}

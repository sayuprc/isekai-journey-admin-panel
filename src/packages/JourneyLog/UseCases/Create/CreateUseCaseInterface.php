<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\Create;

interface CreateUseCaseInterface
{
    public function handle(CreateRequest $request): void;
}

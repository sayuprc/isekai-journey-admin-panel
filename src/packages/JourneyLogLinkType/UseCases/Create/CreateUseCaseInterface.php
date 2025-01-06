<?php

declare(strict_types=1);

namespace JourneyLogLinkType\UseCases\Create;

interface CreateUseCaseInterface
{
    public function handle(CreateRequest $request): void;
}

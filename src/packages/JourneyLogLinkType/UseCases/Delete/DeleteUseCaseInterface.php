<?php

declare(strict_types=1);

namespace JourneyLogLinkType\UseCases\Delete;

interface DeleteUseCaseInterface
{
    public function handle(DeleteRequest $request): void;
}

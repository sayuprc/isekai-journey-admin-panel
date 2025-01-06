<?php

declare(strict_types=1);

namespace JourneyLogLinkType\UseCases\Edit;

interface EditUseCaseInterface
{
    public function handle(EditRequest $request): void;
}

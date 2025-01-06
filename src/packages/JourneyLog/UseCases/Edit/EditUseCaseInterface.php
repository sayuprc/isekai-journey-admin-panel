<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\Edit;

interface EditUseCaseInterface
{
    public function handle(EditRequest $request): void;
}

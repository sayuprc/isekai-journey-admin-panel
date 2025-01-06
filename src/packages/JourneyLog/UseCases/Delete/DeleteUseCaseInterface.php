<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\Delete;

interface DeleteUseCaseInterface
{
    public function handle(DeleteRequest $request): void;
}

<?php

declare(strict_types=1);

namespace Creator\UseCases\Edit;

interface EditUseCaseInterface
{
    public function handle(EditRequest $request): void;
}

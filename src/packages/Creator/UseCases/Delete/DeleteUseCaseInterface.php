<?php

declare(strict_types=1);

namespace Creator\UseCases\Delete;

interface DeleteUseCaseInterface
{
    public function handle(DeleteRequest $request): void;
}

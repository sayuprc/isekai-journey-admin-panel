<?php

declare(strict_types=1);

namespace Creator\UseCases\Create;

interface CreateUseCaseInterface
{
    public function handle(CreateRequest $request): void;
}

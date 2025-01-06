<?php

declare(strict_types=1);

namespace Auth\UseCases\Login;

interface LoginUseCaseInterface
{
    public function handle(LoginRequest $request): LoginResponse;
}

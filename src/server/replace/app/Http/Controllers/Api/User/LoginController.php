<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Middleware\Group\Auth;
use App\Http\Presenters\Api\User\LoginPresenter;
use App\Http\Requests\User\LoginRequest;
use App\Http\Responses\JsonResponse;
use Tempest\Http\Method;
use User\Application\UseCase\Login\LoginUseCaseInterface;

class LoginController
{
    public function __construct(
        private readonly LoginUseCaseInterface $interactor,
        private readonly LoginPresenter $presenter,
    ) {
    }

    #[Auth(Method::POST, '/login')]
    public function handle(LoginRequest $request): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle($request->toInputData()));
    }
}

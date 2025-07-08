<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\User\LoginPresenter;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use User\Application\UseCase\Login\LoginInputData;
use User\Application\UseCase\Login\LoginUseCaseInterface;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthManager $authManager,
        private readonly LoginUseCaseInterface $interactor,
        private readonly LoginPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $credentials = [
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
        ];

        if (! $this->authManager->guard()->attempt($credentials)) {
            return response()->json(status: 401);
        }

        if (is_null($user = $this->authManager->guard()->user()) || ! is_string($identifier = $user->getAuthIdentifier())) {
            return response()->json(status: 500);
        }

        return $this->presenter->present($this->interactor->handle(new LoginInputData($identifier)));
    }
}

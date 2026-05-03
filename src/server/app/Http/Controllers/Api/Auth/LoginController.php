<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Auth\LoginPresenter;
use Auth\Application\UseCase\Login\LoginInputData;
use Auth\Application\UseCase\Login\LoginUseCase;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthManager $authManager,
        private readonly LoginUseCase $useCase,
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

        return $this->presenter->present($this->useCase->handle(new LoginInputData($identifier)));
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\RegisterPresenter;
use Auth\Application\Admin\UseCase\Register\RegisterInputData;
use Auth\Application\Admin\UseCase\Register\RegisterUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function __construct(
        private readonly RegisterUseCase $useCase,
        private readonly RegisterPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new RegisterInputData(
            $request->string('name')->toString(),
            $request->string('email')->toString(),
            $request->string('password')->toString(),
            $request->string('registrationToken')->toString(),
        )));
    }
}

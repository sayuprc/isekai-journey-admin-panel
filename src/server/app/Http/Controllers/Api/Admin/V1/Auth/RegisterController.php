<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use AdminUser\Application\Admin\UseCase\Register\RegisterInputData;
use AdminUser\Application\Admin\UseCase\Register\RegisterUseCase;
use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\RegisterPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function __construct(
        private readonly RegisterUseCase $registerUseCase,
        private readonly RegisterPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $inputData = new RegisterInputData(
            $request->string('token')->toString(),
            $request->string('email')->toString(),
            $request->string('name')->toString(),
            $request->string('password')->toString(),
        );

        return $this->presenter->present($this->registerUseCase->handle($inputData));
    }
}

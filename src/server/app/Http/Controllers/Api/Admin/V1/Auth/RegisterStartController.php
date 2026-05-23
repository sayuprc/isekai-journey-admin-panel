<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use AdminUser\Application\Admin\UseCase\RegisterStart\RegisterStartInputData;
use AdminUser\Application\Admin\UseCase\RegisterStart\RegisterStartUseCase;
use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\RegisterStartPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterStartController extends Controller
{
    public function __construct(
        private readonly RegisterStartUseCase $registerStartUseCase,
        private readonly RegisterStartPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $inputData = new RegisterStartInputData(
            $request->string('token')->toString(),
            $request->string('email')->toString(),
            $request->string('name')->toString(),
        );

        return $this->presenter->present($this->registerStartUseCase->handle($inputData));
    }
}

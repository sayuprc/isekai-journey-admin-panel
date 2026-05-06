<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Auth\LoginStartPresenter;
use Auth\Application\UseCase\LoginStart\LoginStartInputData;
use Auth\Application\UseCase\LoginStart\LoginStartUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginStartController extends Controller
{
    public function __construct(
        private readonly LoginStartUseCase $useCase,
        private readonly LoginStartPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->presenter->present(
            $this->useCase->handle(
                new LoginStartInputData($request->string('email')->toString()),
            ),
        );
    }
}
